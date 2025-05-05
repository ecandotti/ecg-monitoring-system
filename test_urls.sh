#!/bin/bash

echo "Testing URLs..."
echo "======================================"

urls=(
  "http://localhost/"
  "http://localhost/public/"
  "http://localhost/public/index.php"
  "http://localhost/public/pages/configuration.php"
  "http://localhost/public/pages/diagnostic.php"
)

for url in "${urls[@]}"; do
  echo "Testing $url"
  curl -I "$url" | head -n 1
  echo ""
done

echo "======================================"
echo "Test complete!" 