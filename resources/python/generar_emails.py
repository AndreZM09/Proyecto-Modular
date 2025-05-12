import os
import sys
import smtplib
import mysql.connector
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.image import MIMEImage
from dotenv import load_dotenv

# Cargar variables de entorno
load_dotenv()

def main():
    try:
        # Configuración del email
        sender = os.getenv('EMAIL_SENDER')  
        password = os.getenv('EMAIL_PASSWORD')
        
        if not sender or not password:
            print("ERROR: Falta configuracion de email (EMAIL_SENDER o EMAIL_PASSWORD)")
            sys.exit(1)
        
        # Obtener el asunto y descripción, con valores predeterminados simples
        subject = os.getenv('EMAIL_SUBJECT', 'Email de prueba')
        description = os.getenv('EMAIL_DESCRIPTION', 'Este es un email de prueba.')
        
        # Obtener la URL base
        base_url = os.getenv('APP_URL', 'http://127.0.0.1:8000')
        
        # Plantilla HTML simplificada
        html_content = f'''
        <html>
          <body>
            <p>{description}</p>
            <p>Haz clic en la imagen:</p>
            <a href="{base_url}/track-click?email={{email}}">
              <img src="cid:image1" alt="Imagen" style="width:300px;">
            </a>
            <img src="{base_url}/track-open?email={{email}}" width="1" height="1" style="display:none;">
          </body>
        </html>
        '''
        
        # Obtener la lista de destinatarios
        email_list_path = os.getenv('EMAIL_LIST_PATH')
        if not email_list_path or not os.path.exists(email_list_path):
            print("ERROR: Archivo de emails no encontrado")
            sys.exit(1)
            
        # Leer los emails - con encoding explícito
        recipients = []
        with open(email_list_path, 'r', encoding='utf-8') as f:
            for line in f:
                email = line.strip()
                if email:  # Verificar que no esté vacío
                    recipients.append(email)
        
        if not recipients:
            print("ERROR: No se encontraron destinatarios validos")
            sys.exit(1)
            
        print("Se enviaran correos a " + str(len(recipients)) + " destinatarios")
        
        # Obtener la imagen
        image_path = get_latest_image()
        if not image_path:
            print("ERROR: No se pudo obtener la imagen para el correo")
            sys.exit(1)
            
        if not os.path.exists(image_path):
            print("ERROR: La imagen no existe en la ruta especificada")
            sys.exit(1)
            
        # Enviar a cada destinatario
        for recipient in recipients:
            try:
                email_content = html_content.replace("{email}", recipient)
                send_email(sender, password, recipient, subject, email_content, image_path)
                print("Email enviado a: " + recipient)
            except Exception as e:
                print("Error al enviar email: " + str(e))
                
        print("Proceso completado.")
        sys.exit(0)
    
    except Exception as e:
        print("ERROR GENERAL: " + str(e))
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
        cursor.execute("SELECT filename FROM email_images ORDER BY created_at DESC LIMIT 1")
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
            if os.path.exists(public_path):
                print("Usando imagen desde public/storage: " + public_path)
                return public_path
            elif os.path.exists(storage_path):
                print("Usando imagen desde storage/app/public: " + storage_path)
                return storage_path
            else:
                print("Error: La imagen " + result['filename'] + " no se encontró en ninguna ubicación")
        
        return None
        
    except Exception as e:
        print("Error al obtener la imagen de la base de datos: " + str(e))
        return None
    finally:
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()

def send_email(sender, password, recipient, subject, html_content, image_path, 
              smtp_server='smtp.gmail.com', port=587):
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

if __name__ == '__main__':
    main()