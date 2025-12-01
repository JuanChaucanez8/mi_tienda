#!/bin/bash

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Configuración
BASE_URL="http://localhost/mi_tienda"
API_URL="$BASE_URL/api.php"

# Verificar si jq está instalado
if ! command -v jq &> /dev/null; then
    echo -e "${YELLOW}Advertencia: jq no está instalado. Instálalo con: sudo apt install jq${NC}"
    echo "Continuando sin jq..."
    HAS_JQ=false
else
    HAS_JQ=true
fi

# Función para imprimir resultados
print_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓ $2${NC}"
    else
        echo -e "${RED}✗ $2${NC}"
    fi
}

# Función para hacer requests y mostrar resultados
api_request() {
    local method=$1
    local url=$2
    local data=$3
    local description=$4
    
    echo -e "\n${YELLOW}Probando: $description${NC}"
    echo "URL: $url"
    
    if [ -n "$data" ]; then
        echo "Data: $data"
        response=$(curl -s -X "$method" "$url" -H "Content-Type: application/json" -d "$data")
    else
        response=$(curl -s -X "$method" "$url")
    fi
    
    local exit_code=$?
    
    if [ $exit_code -eq 0 ]; then
        echo "Response:"
        if [ "$HAS_JQ" = true ]; then
            echo "$response" | jq .
        else
            echo "$response"
        fi
    else
        echo -e "${RED}Error en la petición curl${NC}"
    fi
    
    print_result $exit_code "$description"
    return $exit_code
}

echo "=== Tests de API para Mi Tienda ==="
echo "Base URL: $BASE_URL"

# Test 1: Listar productos
api_request "GET" "$API_URL?action=list" "" "Listar todos los productos"

# Test 2: Buscar productos
api_request "GET" "$API_URL?action=list&search=laptop" "" "Buscar productos con 'laptop'"

# Test 3: Filtrar por categoría
api_request "GET" "$API_URL?action=list&category=Electrónicos" "" "Filtrar por categoría 'Electrónicos'"

# Test 4: Paginación
api_request "GET" "$API_URL?action=list&page=1&per_page=2" "" "Listar con paginación (página 1, 2 por página)"

# Test 5: Obtener producto por ID
api_request "GET" "$API_URL?action=get&id=1" "" "Obtener producto con ID 1"

# Test 6: Crear nuevo producto
new_product='{
  "name": "Producto de Test API",
  "description": "Este es un producto creado mediante tests de API",
  "price": 49.99,
  "stock": 100,
  "category": "Test",
  "image_path": null
}'
api_request "POST" "$API_URL?action=create" "$new_product" "Crear nuevo producto"

# Obtener ID del producto creado (si jq está disponible)
if [ "$HAS_JQ" = true ]; then
    product_id=$(curl -s -X "GET" "$API_URL?action=list&search=Producto%20de%20Test%20API" | jq -r '.data.products[0].id')
    
    if [ -n "$product_id" ] && [ "$product_id" != "null" ]; then
        # Test 7: Actualizar producto
        update_data='{
          "name": "Producto de Test API Actualizado",
          "price": 39.99,
          "stock": 50
        }'
        api_request "PUT" "$API_URL?action=update&id=$product_id" "$update_data" "Actualizar producto creado"
        
        # Test 8: Eliminar producto
        api_request "DELETE" "$API_URL?action=delete&id=$product_id" "" "Eliminar producto creado"
    fi
fi

# Test 9: Producto no encontrado
api_request "GET" "$API_URL?action=get&id=9999" "" "Intentar obtener producto inexistente"

# Test 10: Acción no válida
api_request "GET" "$API_URL?action=invalid" "" "Intentar acción no válida"

echo -e "\n${YELLOW}=== Tests completados ===${NC}"