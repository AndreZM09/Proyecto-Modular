import os
import smtplib
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.image import MIMEImage
from dotenv import load_dotenv

# Cargar variables de entorno
load_dotenv()

def send_email_with_image(sender, password, recipient, subject,
                          html_content, image_path, image_cid,
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

    try:
        with open(image_path, 'rb') as img_file:
            img = MIMEImage(img_file.read())
            img.add_header('Content-ID', f'<{image_cid}>')
            img.add_header('Content-Disposition', 'inline', filename=image_path)
            msg.attach(img)
    except Exception as e:
        print("Error al adjuntar la imagen:", e)
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
    recipient = os.getenv('EMAIL_RECIPIENT')
    subject = 'Prueba de envío de email'

    # Usamos la URL base desde el .env
    base_url = os.getenv('APP_URL', 'http://127.0.0.1:8000')

    html_content = f'''
    <html>
      <body>
        <p>Haz clic en la imagen para registrar el clic:</p>
        <a href="{base_url}/track-click?email={recipient}">
          <img src="cid:image1" alt="Imagen de ejemplo" style="width:300px;">
        </a>
        <!-- Pixel de seguimiento invisible para registrar apertura -->
        <img src="{base_url}/track-open?email={recipient}" width="1" height="1" style="display:none;">
      </body>
    </html>
    '''
    
    # Cargar la ruta de la imagen desde el .env
    image_path = os.getenv('EMAIL_IMAGE_PATH')
    image_cid = 'image1'

    send_email_with_image(sender, password, recipient, subject, html_content, image_path, image_cid)