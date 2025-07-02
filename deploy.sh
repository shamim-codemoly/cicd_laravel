#!/bin/bash

DOMAIN=$1
PORT=$2
EMAIL=$3
CONFIG=$4

# Optional: echo inputs for debugging
echo "Domain: $DOMAIN"
echo "Email: $EMAIL"
echo "Port: $PORT"
echo "Config: $CONFIG"

# Save to file (e.g., generated.php)
cat <<EOL > koba_samso.php
<?php
echo "Deployed GTM Container<br>";
echo "Domain: $DOMAIN<br>";
echo "Email: $EMAIL<br>";
echo "Port: $PORT<br>";
echo "Config: $CONFIG<br>";
?>
EOL

echo "gtm_info.php created successfully."
