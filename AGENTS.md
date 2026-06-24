# AGENTS.md

## Proyecto

**Simple Sales System**

Prueba técnica desarrollada con:

- Laravel 13
- Filament 5
- PHP 8.5
- SQLite
- Docker Compose (opcional al final)

El objetivo es construir una aplicación de gestión de ventas pequeña pero limpia, enfocada en calidad de código, mantenibilidad y mejores prácticas de Laravel.

---

## Principios Generales

- Preferir legibilidad sobre código ingenioso.
- Mantener la implementación simple.
- Evitar optimización prematura.
- Preferir convenciones de Laravel sobre abstracciones personalizadas.
- Usar relaciones de Eloquent siempre que sea posible.
- Usar tipado estricto cuando sea apropiado.
- Mantener clases pequeñas y enfocadas.
- Seguir principios SOLID de forma pragmática.

---

## Arquitectura

Usar una arquitectura ligera en capas:

```text
Filament Resource
        ↓
Action (caso de uso de negocio)
        ↓
Service (operaciones de dominio/negocio)
        ↓
Eloquent Models
```

Evitar poner lógica de negocio dentro de:

- Filament Resources
- Controllers
- Form callbacks
- Views

---

## Models

Los modelos deben:

- Definir relaciones.
- Definir casts.
- Contener solo métodos helper simples.
- Evitar lógica de negocio pesada.

Ejemplo:

```php
public function items(): HasMany
{
    return $this->hasMany(OrderProduct::class);
}
```

Evitar:

```php
public function processEntireOrder()
{
   // ...
}
```

---

## Base de Datos

Usar SQLite durante desarrollo.

Archivo de base de datos:

```text
database/database.sqlite
```

No committear:

```text
database/database.sqlite
```

La base de datos debe ser reproducible mediante:

```bash
php artisan migrate --seed
```

---

## Convenciones de Nomenclatura

Modelos en singular:

```text
Product, Order, OrderProduct
```

Tablas en plural:

```text
products, orders, order_products
```

Nombres de Resources:

```text
ProductResource, OrderResource
```

Actions:

```text
CreateOrderAction, CancelOrderAction
```

Services:

```text
OrderService, ProductService
```

Métodos en inglés, comentarios y commits en inglés.

---

## Filament Guidelines

Los Resources deben:

- Ser delgados.
- Delegar lógica de negocio a Actions o Services.
- Usar relationship fields cuando sea posible.
- Usar Repeaters para order items.

Evitar:

- Consultas a base de datos dentro de form callbacks.
- Cálculos complejos dentro de formularios de Filament.

Preferir:

```text
Resource → Action → Service → Model
```

---

## Módulo de Productos

Campos:

```text
name, description, price, stock
```

Requerimientos:

- CRUD completo.
- Validación.
- Seeder.
- Factory.

Validación:

```text
name     => required|string|max:255
price    => numeric|min:0
stock    => integer|min:0
```

---

## Módulo de Órdenes

Entidades:

```text
Order, OrderProduct
```

Relaciones:

```text
Order
  hasMany(OrderProduct)

OrderProduct
  belongsTo(Order)
  belongsTo(Product)
```

Campos de Order:

```text
total (decimal)
```

Campos de OrderProduct:

```text
product_id    (foreign key)
quantity      (integer)
product_price (decimal, copia del precio al momento de crear)
subtotal      (decimal, quantity * product_price)
```

---

## Reglas de Negocio

Al crear una orden:

1. Validar stock suficiente para cada producto.
2. Calcular subtotal = quantity * product_price.
3. Calcular total de la orden = suma de subtotals.
4. Disminuir el stock de cada producto.
5. Persistir todo dentro de una transacción de base de datos.

Al cancelar una orden:

1. Restaurar el stock de cada producto.
2. Persistir dentro de una transacción.

---

## Transacciones

Siempre que se modifiquen múltiples modelos, usar:

```php
DB::transaction(function () {
    // ...
});
```

Ejemplos:

- Crear orden + actualizar stock.
- Cancelar orden + restaurar stock.

---

## Commits

- Usar commits atómicos (un cambio lógico por commit).
- Mensajes en inglés, presente imperativo.
- Prefijos recomendados: `feat:`, `fix:`, `refactor:`, `chore:`, `docs:`.
- No committear archivos de entorno (.env) ni la base de datos SQLite.

---

## Testing

- Los tests deben estar en `tests/Feature` o `tests/Unit`.
- Usar factories para crear datos de prueba.
- Probar casos felices y casos borde (validación, stock insuficiente, etc.).

Comandos:

```bash
php artisan test
```

---

## Estilo de Código

- Seguir PSR-12.
- Usar `declare(strict_types=1)` en archivos de código.
- Sin debug code (`dd()`, `dump()`, `ray()`) en commits.
- Sin comentarios innecesarios; el código debe ser auto-documentado.

---

## Comandos Útiles

```bash
php artisan migrate --seed            # Migrar y sembrar base de datos
php artisan make:model ModelName      # Crear modelo
php artisan make:filament-resource    # Crear Filament Resource
php artisan test                      # Ejecutar tests
composer run lint                     # Verificar estilo de código
composer run typecheck                # Verificar tipos (si aplica)
```

---

## Definition of Done

Una funcionalidad está terminada cuando:

- El código compila sin errores.
- La funcionalidad funciona correctamente.
- Existe validación.
- Existe seeder si aplica.
- Las reglas de negocio se respetan.
- No queda código de debug.
- Los tests pasan.
- Se realizó el commit en git.

---

## Objetivo Final

Entregar un sistema de ventas limpio, mantenible y completamente funcional.

Priorizar:

1. Correctitud
2. Simplicidad
3. Mantenibilidad
4. Experiencia de desarrollo
5. Infraestructura
