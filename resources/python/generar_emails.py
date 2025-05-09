import os
import smtplib
import mysql.connector
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.image import MIMEImage
from dotenv import load_dotenv

# Cargar variables de entorno
load_dotenv()

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
            image_path = os.path.join('storage', 'app', 'public', 'email_images', result['filename'])
            return image_path
        return None
        
    except Exception as e:
        print("Error al obtener la imagen de la base de datos:", e)
        return None
    finally:
        if 'connection' in locals() and connection.is_connected():
            cursor.close()
            connection.close()

def send_email_with_image(sender, password, recipient, subject,
                          html_content, image_cid,
                          smtp_server='smtp.gmail.com', port=587):
    msg = MIMEMultipart('related')
    msg['From'] = sender
    msg['To'] = recipient
    msg['Subject'] = subject

    msg_alternative = MIMEMultipart('alternative')
    msg.attach(msg_alternative)

    text_part = MIMEText('Este correo contiene una imagen. Abre el mensaje en un cliente que soporte HTML.', 'plain')
    msg_alternative.attach(text_part)

    html_part = MIMEText(html_content, 'html')
    msg_alternative.attach(html_part)

    # Obtener la última imagen subida
    image_path = get_latest_image()
    
    if image_path and os.path.exists(image_path):
        try:
            with open(image_path, 'rb') as img_file:
                img = MIMEImage(img_file.read())
                img.add_header('Content-ID', f'<{image_cid}>')
                img.add_header('Content-Disposition', 'inline', filename=os.path.basename(image_path))
                msg.attach(img)
        except Exception as e:
            print("Error al adjuntar la imagen:", e)
            return
    else:
        print("No se encontró una imagen configurada")
        return

    try:
        server = smtplib.SMTP(smtp_server, port)
        server.starttls()
        server.login(sender, password)
        server.sendmail(sender, recipient, msg.as_string())
        server.quit()
        print("Email enviado exitosamente!")
    except Exception as e:
        print("Error al enviar el email:", e)

if __name__ == '__main__':
    sender = os.getenv('EMAIL_SENDER')  
    password = os.getenv('EMAIL_PASSWORD')
    # Lista de destinatarios
    recipients = ['andre_zm09@hotmail.com', 'andre.zurita2865@alumnos.udg.mx']
    subject = 'Prueba de envío de email'

    # Usamos la URL base desde el .env
    base_url = os.getenv('APP_URL', 'http://127.0.0.1:8000')

    html_content = f'''
    <html>
      <body>
        <p>Haz clic en la imagen para registrar el clic:</p>
        <a href="{base_url}/track-click?email={recipients[0]}">
          <img src="cid:image1" alt="Imagen de ejemplo" style="width:300px;">
        </a>
        <!-- Pixel de seguimiento invisible para registrar apertura -->
        <img src="{base_url}/track-open?email={recipients[0]}" width="1" height="1" style="display:none;">
      </body>
    </html>
    '''
    
    image_cid = 'image1'

    # Enviar el correo a cada destinatario
    for recipient in recipients:
        print(f"Enviando correo a: {recipient}")
        send_email_with_image(sender, password, recipient, subject, html_content, image_cid)