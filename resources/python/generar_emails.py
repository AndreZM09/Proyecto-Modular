import os
import sys
import smtplib
import mysql.connector
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.image import MIMEImage
from dotenv import load_dotenv
import base64

# Cargar variables de entorno con encoding específico
load_dotenv(encoding='utf-8-sig')

def main():
    try:
        # Configuración del email
        sender = os.getenv('EMAIL_SENDER')  
        password = os.getenv('EMAIL_PASSWORD')
        
        if not sender or not password:
            print("ERROR: Falta configuracion de email (EMAIL_SENDER o EMAIL_PASSWORD)")
            print(f"EMAIL_SENDER: {sender}")
            print(f"EMAIL_PASSWORD: {'***' if password else 'None'}")
            sys.exit(1)
        
        # Obtener el asunto y descripción, con valores predeterminados simples
        # Se obtienen las versiones Base64 y se decodifican
        encoded_subject = os.getenv('EMAIL_SUBJECT_B64')
        encoded_description = os.getenv('EMAIL_DESCRIPTION_B64')
        
        subject = "Email de prueba"
        description = "Este es un email de prueba."
        
        if encoded_subject:
            try:
                subject = base64.b64decode(encoded_subject).decode('utf-8')
            except Exception as e:
                print(f"ADVERTENCIA: No se pudo decodificar el asunto B64: {e}")
        
        if encoded_description:
            try:
                description = base64.b64decode(encoded_description).decode('utf-8')
            except Exception as e:
                print(f"ADVERTENCIA: No se pudo decodificar la descripción B64: {e}")
        
        # Obtener la URL base
        base_url = os.getenv('APP_URL', 'http://127.0.0.1:8000')
        
        print(f"Configuracion cargada:")
        print(f"- Sender: {sender}")
        print(f"- Subject: {subject}")
        print(f"- Base URL: {base_url}")
        
        # Obtener la imagen y su ID
        image_info = get_latest_image()
        if not image_info or 'path' not in image_info:
            print("ERROR: No se pudo obtener la imagen para el correo")
            sys.exit(1)
            
        image_path = image_info['path']
        image_id = image_info['id']
        
        if not os.path.exists(image_path):
            print("ERROR: La imagen no existe en la ruta especificada")
            print(f"Ruta verificada: {image_path}")
            sys.exit(1)
        
        print(f"Imagen encontrada: {image_path}")
        
        # Plantilla HTML con el ID de la imagen incluido en los enlaces
        html_content = f'''
        <html>
          <body>
            <p>{description}</p>
            <p>Haz clic en la imagen:</p>
            <a href="{base_url}/track-click?email={{email}}&img_id={image_id}">
              <img src="cid:image1" alt="Imagen" style="width:300px;">
            </a>
            <img src="{base_url}/track-open?email={{email}}&img_id={image_id}" width="1" height="1" style="display:none;">
          </body>
        </html>
        '''
        
        # Obtener los datos de emails (JSON con datos individuales o lista simple)
        email_data_path = os.getenv('EMAIL_DATA_PATH')
        email_list_path = os.getenv('EMAIL_LIST_PATH')
        
        recipients = []
        
        if email_data_path and os.path.exists(email_data_path):
            # Cargar datos individuales desde JSON
            try:
                import json
                with open(email_data_path, 'r', encoding='utf-8') as f:
                    email_data = json.load(f)
                for data in email_data:
                    recipients.append({
                        'email': data['email'],
                        'subject': data['subject'],
                        'description': data['message']
                    })
                print(f"Cargados {len(recipients)} destinatarios con datos individuales")
            except Exception as e:
                print(f"ERROR leyendo archivo JSON: {e}")
                sys.exit(1)
        elif email_list_path and os.path.exists(email_list_path):
            # Usar método anterior con datos globales
            try:
                with open(email_list_path, 'r', encoding='utf-8') as f:
                    for line in f:
                        email = line.strip()
                        if email:
                            recipients.append({
                                'email': email,
                                'subject': subject,
                                'description': description
                            })
            except UnicodeDecodeError:
                try:
                    with open(email_list_path, 'r', encoding='latin-1') as f:
                        for line in f:
                            email = line.strip()
                            if email:
                                recipients.append({
                                    'email': email,
                                    'subject': subject,
                                    'description': description
                                })
                except Exception as e:
                    print(f"ERROR leyendo archivo de emails: {e}")
                    sys.exit(1)
        else:
            print(f"ERROR: No se encontró archivo de emails")
            sys.exit(1)
        
        if not recipients:
            print("ERROR: No se encontraron destinatarios validos")
            sys.exit(1)
            
        print(f"Se enviaran correos a {len(recipients)} destinatarios")
        print(f"Usando la imagen con ID: {image_id}")
        
        # Enviar a cada destinatario con su asunto y descripción individual
        successful_sends = 0
        for recipient_data in recipients:
            try:
                email = recipient_data['email']
                # Asegurarse de decodificar asunto y descripción si vienen de un archivo JSON procesado previamente
                # o si son pasados directamente como ya decodificados del entorno
                email_subject = recipient_data['subject']
                email_description = recipient_data['description']
                
                html_content = f'''
                <html>
                  <body>
                    <p>{email_description}</p>
                    <p>Haz clic en la imagen:</p>
                    <a href="{base_url}/track-click?email={email}&img_id={image_id}">
                      <img src="cid:image1" alt="Imagen" style="width:300px;">
                    </a>
                    <img src="{base_url}/track-open?email={email}&img_id={image_id}" width="1" height="1" style="display:none;">
                  </body>
                </html>
                '''
                
                send_email(sender, password, email, email_subject, html_content, image_path)
                print(f"Email enviado a: {email} - Asunto: {email_subject}")
                successful_sends += 1
            except Exception as e:
                print(f"Error al enviar email a {recipient_data.get('email', 'unknown')}: {str(e)}")
                
        print(f"Proceso completado. {successful_sends}/{len(recipients)} correos enviados exitosamente.")
        sys.exit(0)
    
    except Exception as e:
        print(f"ERROR GENERAL: {str(e)}")
        import traceback
        traceback.print_exc()
        sys.exit(1)

def get_latest_image():
    try:
        connection = mysql.connector.connect(
            host=os.getenv('DB_HOST', 'localhost'),
            user=os.getenv('DB_USERNAME', 'root'),
            password=os.getenv('DB_PASSWORD', ''),
            database=os.getenv('DB_DATABASE', 'modular')
        )
        
        cursor = connection.cursor(dictionary=True)
        cursor.execute("SELECT id, filename FROM email_images ORDER BY created_at DESC LIMIT 1")
        result = cursor.fetchone()
        
        if result:
            # Verificar si existe en public/storage
            public_path = os.path.join(
                os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))),
                'public', 'storage', 'email_images', result['filename']
            )
            
            # Verificar si existe en storage/app/public
            storage_path = os.path.join(
                os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))),
                'storage', 'app', 'public', 'email_images', result['filename']
            )
            
            # Comprobar cuál existe y devolverlo
            image_info = {'id': result['id']}
            
            if os.path.exists(public_path):
                print(f"Usando imagen desde public/storage: {public_path}")
                image_info['path'] = public_path
                return image_info
            elif os.path.exists(storage_path):
                print(f"Usando imagen desde storage/app/public: {storage_path}")
                image_info['path'] = storage_path
                return image_info
            else:
                print(f"Error: La imagen {result['filename']} no se encontró en ninguna ubicación")
                print(f"Rutas verificadas:")
                print(f"  - {public_path}")
                print(f"  - {storage_path}")
        
        return None
        
    except Exception as e:
        print(f"Error al obtener la imagen de la base de datos: {str(e)}")
        return None
    finally:
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()

def send_email(sender, password, recipient, subject, html_content, image_path, 
              smtp_server='smtp.gmail.com', port=587):
    try:
        msg = MIMEMultipart('related')
        msg['From'] = sender
        msg['To'] = recipient
        msg['Subject'] = subject

        msg_alternative = MIMEMultipart('alternative')
        msg.attach(msg_alternative)

        text_part = MIMEText('Este correo contiene una imagen.', 'plain')
        msg_alternative.attach(text_part)

        html_part = MIMEText(html_content, 'html', 'utf-8')  # Especificar codificación utf-8
        msg_alternative.attach(html_part)

        # Adjuntar la imagen
        with open(image_path, 'rb') as img_file:
            img = MIMEImage(img_file.read())
            img.add_header('Content-ID', '<image1>')
            img.add_header('Content-Disposition', 'inline', filename=os.path.basename(image_path))
            msg.attach(img)

        # Enviar el correo
        server = smtplib.SMTP(smtp_server, port)
        server.starttls()
        server.login(sender, password)
        server.sendmail(sender, recipient, msg.as_string())
        server.quit()
    except Exception as e:
        raise Exception(f"Error en send_email: {str(e)}")

if __name__ == '__main__':
    main()