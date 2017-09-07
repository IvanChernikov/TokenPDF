# TokenPDF

Requires PHP5+, MySQL/MariaDB

Everything can run off the same server. 
Client: /index.html 
Server: contents of /api/

# Configuration
Change settings in '/api/config.php':
- DB_HOSTNAME, DB_USERNAME, etc..
- TOKEN_EXPIRATION_TIME (default 5mins)

# Database
Run script '/api/sql/test_sql.sql' to setup database.
Note that different credentials have different permissions.
