# Simple Sales System

Sistema de gestión de ventas construido con **Laravel 13** + **Filament 5**.

## Requisitos

- PHP 8.5+
- Composer
- Node.js 18+

## Instalación

```bash
# Clonar el repositorio
git clone <repo-url> simple-sales-system
cd simple-sales-system

# Instalar dependencias PHP
composer install

# Instalar dependencias frontend
yarn install

# Configurar entorno
cp .env.example .env
php artisan key:generate

# Migrar y seedear la base de datos
php artisan migrate --seed

# Compilar assets
yarn build
```

## Inicio rápido

```bash
php artisan serve
# o usando Vite para desarrollo frontend:
yarn dev
```

Acceder a `http://localhost:8000/admin`

## Credenciales por defecto

| Rol    | Email                 | Contraseña   |
|--------|-----------------------|--------------|
| Admin  | admin@example.com     | admin123     |
| User   | example@example.com   | example123   |

## Arquitectura

```
Filament Resource  →  Page  →  Service  →  Eloquent Model
```

- **Resources**: capa delgada que delega en schemas/tables/pages.
- **Pages**: orquestan el flujo e invocan servicios.
- **Services**: lógica de negocio (ej: `InventoryService`, `OrderService`).
- **Models**: relaciones, casts y helpers simples — sin lógica de negocio.

## Módulos

### Productos
- CRUD completo con validación (`name`, `price ≥ 0`, `stock ≥ 0`).
- Precios almacenados como enteros (centavos) vía `MoneyCast`.
- IDs con UUID.

### Órdenes
- Creación con ítems dinámicos (Repeater).
- Estados: `pending`, `completed`, `refunded`.
- Políticas por rol (admin/user).

## Reglas de negocio

1. Validar stock disponible antes de crear/editar una orden.
2. Calcular subtotal = cantidad × precio unitario.
3. Calcular total = suma de subtotales.
4. Descontar stock al confirmar la orden.
5. Todo dentro de transacciones de base de datos.

## Tests

```bash
php artisan test
```

## Stack

- **Laravel 13** + SQLite
- **Filament 5** (Admin Panel)
- **TailwindCSS 4** + **Vite**
- **Pest** (testing)
