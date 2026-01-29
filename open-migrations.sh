#!/bin/bash

# Script pentru a deschide migrațiile cu nano

cd /var/www/volta-dashboard

echo "Ce fișier vrei să deschizi?"
echo "1. 2025_12_04_064148_add_name_to_users_table.php"
echo "2. 2025_12_04_064451_add_timestamps_to_users_table.php"
echo "3. Ambele (unul după altul)"
echo ""
read -p "Alege opțiunea (1/2/3): " choice

case $choice in
    1)
        nano database/migrations/2025_12_04_064148_add_name_to_users_table.php
        ;;
    2)
        nano database/migrations/2025_12_04_064451_add_timestamps_to_users_table.php
        ;;
    3)
        nano database/migrations/2025_12_04_064148_add_name_to_users_table.php
        nano database/migrations/2025_12_04_064451_add_timestamps_to_users_table.php
        ;;
    *)
        echo "Opțiune invalidă"
        exit 1
        ;;
esac
