# Symfony AI Product Agent

Código fuente del artículo **"Construyendo un Agente de IA con Symfony"**.

Un agente conversacional para una tienda en línea ficticia, construido con Symfony 8, el componente Symfony AI y el modelo GPT OSS 120B (`openai/gpt-oss-120b`) a través de la API de Groq. 

**Características principales:**
- **Streaming en tiempo real:** Implementado con Server-Sent Events (SSE) para una respuesta instantánea.
- **Tools dinámicas:** Búsqueda, precios y categorías integradas con el agente.
- **Historial persistente:** Gestión de contexto mediante sesiones de Symfony.
- **Frontend nativo:** Interfaz construida con Vanilla JS y CSS moderno, sin frameworks pesados.

> **Nota:** Actualizado a [Symfony AI v0.14.1](https://github.com/symfony/ai/releases/tag/v0.14.1). Incorpora el nuevo modelo lazy `Execution` con streaming tipado (`asTextStream()`), resiliencia avanzada ante límites del proveedor (`RateLimitExceededException` con `retry_after`), `MaxOutputTokensException` y preservación defensiva del historial de sesión.

---

## Documentación Detallada

Puedes encontrar guías paso a paso de la implementación en los siguientes artículos:

1.  **[Symfony AI en la práctica: agentes, tools y modelos gratuitos desde PHP](https://juredev.com/blog/2026/04/symfony-ai-agentes-tools-php/)**
2.  **[Symfony AI en la práctica (II): chat conversacional con historial, múltiples tools y Vanilla JS](https://juredev.com/blog/2026/04/symfony-ai-practica-chat-con-historial-tools-y-vanilla-js/)**
3.  **[Symfony AI en la práctica (III): Streaming real con SSE y gestión de Tools anidadas](https://juredev.com/blog/2026/04/symfony-ai-practica-iii-streaming-con-sse-gestion-de-tools-anidadas/)**
- **[Actualización técnica: Migración a Symfony AI v0.12, resiliencia y selección de modelos](https://juredev.com/blog/2026/08/symfony-ai-en-la-practica-migracion-a-v0-12/)**
- **[Actualización técnica: Migración a Symfony AI v0.14.1, ejecución lazy y resiliencia ante Rate Limits](https://juredev.com/blog/2026/10/symfony-ai-migracion-v0-14-1-ejecucion-lazy-y-resiliencia-rate-limits/)**

---

## Requisitos

- PHP 8.4+
- Composer
- Una clave API gratuita de [Groq](https://console.groq.com/)

---

## Instalación

```bash
git clone https://github.com/tu-usuario/symfony-ai-product-agent.git
cd symfony-ai-product-agent

composer install

cp .env .env.local
# Añadir en .env.local:
# GROQ_API_KEY=gsk_tu_clave_aqui

symfony server:start
```

Abrir en el navegador: `http://127.0.0.1:8000/chat-demo`

---

## Estructura

```
src/
├── AI/
│   ├── ProductChatService.php      # Gestiona el historial de sesión y construye el MessageBag
│   └── Tool/
│       ├── ProductSearchTool.php   # Herramienta: busca productos por categoría (paginado)
│       ├── ProductPriceTool.php    # Herramienta: devuelve el precio de un SKU concreto
│       └── ProductCategoryTool.php # Herramienta: devuelve las categorías disponibles
├── Controller/
│   └── ProductChatController.php   # Endpoint POST /api/chat
├── Entity/
│   └── Product.php
└── Repository/
    └── ProductRepository.php       # Catálogo mock: 20 productos, 5 por categoría

templates/chat/demo.html.twig       # Interfaz del chat
config/packages/ai.yaml             # Configuración del agente y las herramientas
```

---

## Herramientas del Agente

| Nombre | Parámetros | Descripción |
|---|---|---|
| `search` | `category` (string), `page` (string, por defecto "1") | Busca productos por categoría (slug). Devuelve 3 resultados por página. |
| `price` | `id` (string) | Devuelve el precio de un producto a partir de su SKU. |
| `categories` | - | Devuelve la lista de categorías disponibles en la tienda. |

---

## Diagrama de Arquitectura

```mermaid
flowchart TD
    A["Usuario / Frontend Vanilla JS"] -->|"POST /api/chat (SSE)"| B[ProductChatController]
    
    subgraph Controller ["Controlador & Resiliencia"]
        B -->|Captura granular| E1[ExceedContextSizeException]
        B -->|Captura granular| E2[RateLimitExceededException]
        B -->|Captura granular| E3[ServerException]
    end

    B --> C[ProductChatService]
    C -->|clona MessageBag| D["AgentInterface (v0.14.1)"]
    D -->|retorna| Ex["Execution (Lazy)"]
    Ex -->|asTextStream| C
    
    D <-->|chat/completions| G["Groq API"]
    D -->|Tool calls automáticas| Tools
    
    subgraph Tools ["Herramientas #[AsTool]"]
        T1[ProductSearchTool]
        T2[ProductPriceTool]
        T3[ProductCategoryTool]
    end
    
    Tools --> R[ProductRepository]
    C -->|"TextDelta Stream SSE"| B
    B -->|"EventSource SSE"| A
```

---

## Ejemplo de Conversación Real

Flujo real del asistente interactuando con herramientas, paginando y aplicando reglas de negocio:

```text
Usuario: hola
Asistente: ¡Hola! Soy el asistente de la tienda. ¿En qué puedo ayudarte hoy?
Puedo mostrarte nuestras categorías disponibles o buscar productos específicos (como teléfonos, portátiles, auriculares o relojes).

Usuario: tienes relojes?
[Tool: search(category: "reloj", page: "1")]
Asistente: Sí, tenemos varios relojes disponibles. Actualmente tenemos:
1. Reloj Inteligente Serie 5 (SKU: SKU-400) — 249.99 EUR
2. Reloj Deportivo Básico (SKU: SKU-401) — 49.50 EUR
3. Reloj Inteligente Premium (SKU: SKU-402) — 399.00 EUR
¿Te gustaría conocer más detalles sobre alguno de ellos o quieres ver más opciones en la página 2?

Usuario: pasa a la pagina 2
[Tool: search(category: "reloj", page: "2")]
Asistente: Aquí tienes la página 2 de relojes:
1. Reloj GPS para Niños (SKU: SKU-403) — 89.99 EUR
2. Reloj Todoterreno (SKU: SKU-404) — 299.50 EUR

Usuario: cual es el mas barato?
Asistente: El reloj más barato de todos los que hay en la tienda es el Reloj Deportivo Básico (SKU: SKU-401), con un precio de 49.50 EUR.

Usuario: quiero comprarlo
Asistente: Lamentablemente, no puedo procesar pedidos ni realizar pagos (Regla NO SALES). Para adquirir el Reloj Deportivo Básico, te recomiendo realizar la compra directamente a través de la web o aplicación de la tienda.
```

---

## Licencia

MIT
