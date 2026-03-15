================================================================================
  ROUNDCUBE WEBMAIL - DOCKER COMPOSE STACK
================================================================================

  Puerto expuesto: 8080 (para reverse proxy)
  Configura tu mail server en el archivo .env (variable MAIL_HOST)

================================================================================
  QUE HACE ESTE DOCKER COMPOSE
================================================================================

Este stack levanta 3 servicios que trabajan juntos para ofrecer un cliente
de correo web (webmail) rapido y confiable:

  1. ROUNDCUBE (roundcube/roundcubemail:latest-apache)
     - Cliente de correo web con interfaz moderna (skin Elastic).
     - Se conecta via IMAP (ssl://imap.tudominio.com:993) para leer correo.
     - Se conecta via SMTP (tls://imap.tudominio.com:587) para enviar correo.
     - Plugins incluidos: archive, zipdownload, managesieve.
     - Limite de archivos adjuntos: 25 MB.

  2. MYSQL 8.4 (mysql:8.4)
     - Base de datos donde Roundcube guarda:
       * Contactos y libretas de direcciones.
       * Identidades de usuario (firmas, nombres).
       * Preferencias y configuraciones de cada usuario.
       * Busquedas guardadas y filtros.
     - Los datos persisten en el directorio ./db-data/

  3. REDIS 7 (redis:7-alpine)
     - Cache en memoria ultra rapido que almacena:
       * Sesiones de usuario (en lugar de guardarlas en MySQL).
       * Cache del indice IMAP (lista de mensajes, carpetas).
       * Cache del contenido de mensajes.
     - Configurado con 64 MB de memoria maxima y politica LRU
       (elimina automaticamente los datos menos usados cuando se llena).
     - Los datos persisten en el directorio ./redis-data/

================================================================================
  VENTAJAS DE ESTE STACK
================================================================================

  RENDIMIENTO
  -----------
  - Redis reduce las consultas a MySQL entre un 60-80% al cachear sesiones
    y datos IMAP en memoria. Lectura en ~1ms vs ~10-50ms de MySQL.
  - El indice IMAP se cachea por 10 dias, asi que al abrir el buzon no
    necesita consultar el servidor de correo cada vez.
  - Los mensajes leidos se cachean, la segunda lectura es instantanea.
  - Paginas de 50 mensajes para carga rapida del inbox.

  CONFIABILIDAD
  -------------
  - Los 3 servicios tienen "restart: unless-stopped", es decir, se levantan
    solos despues de un reboot del servidor.
  - MySQL y Redis tienen healthchecks configurados. Roundcube NO arranca
    hasta que ambos esten saludables (evita errores de conexion al inicio).
  - Los datos de MySQL y Redis persisten en disco, no se pierden al
    reiniciar contenedores.

  SEGURIDAD
  ---------
  - Las credenciales estan en un archivo .env separado, no en el compose.
  - La red interna (roundcube-net) es privada; solo el puerto 8080 esta
    expuesto al host para conectar con el reverse proxy.
  - Verificacion de IP activa: si la IP del usuario cambia durante la
    sesion, se cierra automaticamente.
  - Redis no esta expuesto a internet, solo accesible dentro de la red
    Docker interna.

  MANTENIMIENTO
  -------------
  - Stack 100% en Docker, no contamina el sistema operativo.
  - Actualizar es tan simple como: docker compose pull && docker compose up -d
  - Backup de datos: copiar los directorios ./db-data/ y ./redis-data/
  - Logs centralizados: docker compose logs -f

================================================================================
  CONFIGURACION INICIAL
================================================================================

  1. Copiar el archivo .env.example a .env:
       cp .env.example .env

  2. Editar .env y cambiar los valores:
       MAIL_HOST=imap.tudominio.com    <-- tu servidor de correo IMAP/SMTP
       MYSQL_ROOT_PASSWORD=...         <-- cambiar por una password segura
       MYSQL_PASSWORD=...              <-- cambiar por una password segura

  3. Levantar el stack:
       docker compose up -d

================================================================================
  ESTRUCTURA DE ARCHIVOS
================================================================================

  roundcube-webmail/
  |-- docker-compose.yml      Orquestacion de los 3 servicios
  |-- .env.example            Plantilla de configuracion (subir a git)
  |-- .env                    Credenciales reales (NO subir a git)
  |-- config/
  |   |-- custom.inc.php      Configuracion de Redis y ajustes de rendimiento
  |-- db-data/                Datos persistentes de MySQL (auto-creado)
  |-- redis-data/             Datos persistentes de Redis (auto-creado)
  |-- www/                    Codigo de Roundcube (auto-creado)

================================================================================
  COMANDOS UTILES
================================================================================

  Levantar el stack:          docker compose up -d
  Ver estado:                 docker compose ps
  Ver logs en tiempo real:    docker compose logs -f
  Ver logs de un servicio:    docker compose logs -f roundcubemail
  Detener todo:               docker compose down
  Actualizar imagenes:        docker compose pull && docker compose up -d
  Reiniciar un servicio:      docker compose restart roundcubemail

================================================================================
  CONFIGURACION DEL REVERSE PROXY
================================================================================

  Apuntar al backend:  http://127.0.0.1:8080

  Ejemplo Nginx:

    server {
        listen 443 ssl;
        server_name webmail.tudominio.com;

        ssl_certificate     /ruta/al/certificado.pem;
        ssl_certificate_key /ruta/a/la/llave.pem;

        location / {
            proxy_pass http://127.0.0.1:8080;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Forwarded-Proto $scheme;
            client_max_body_size 25M;
        }
    }

================================================================================
  NOTAS
================================================================================

  - Si el servidor IMAP usa STARTTLS en puerto 143 en vez de SSL implicito
    en 993, cambiar en docker-compose.yml:
      ROUNDCUBEMAIL_DEFAULT_HOST: tls://${MAIL_HOST}
      ROUNDCUBEMAIL_DEFAULT_PORT: "143"

  - Si el servidor SMTP usa SSL implicito en puerto 465 en vez de STARTTLS
    en 587, cambiar en docker-compose.yml:
      ROUNDCUBEMAIL_SMTP_SERVER: ssl://${MAIL_HOST}
      ROUNDCUBEMAIL_SMTP_PORT: "465"

================================================================================
  CREDITOS
================================================================================

  Configurado por Guillermo Dewey
  https://ofik.com

================================================================================
