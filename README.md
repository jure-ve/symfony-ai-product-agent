# Symfony AI Product Agent

Código fuente del artículo **"Construyendo un Agente de IA con Symfony"**.

Un agente conversacional para una tienda en línea ficticia, construido con Symfony 8, el componente Symfony AI y el modelo Llama 3.3 a través de la API de Groq. El frontend es una página simple con Vanilla JS que consume el backend mediante la Fetch API.

---

## Requisitos

- PHP 8.3+
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
│       ├── ProductSearchTool.php   # Herramienta: busca productos por nombre (paginado)
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
| `search` | `query` (string), `page` (string, por defecto "1") | Busca productos por nombre o categoría. Devuelve 3 resultados por página. |
| `price` | `id` (string) | Devuelve el precio de un producto a partir de su SKU. |
| `categories` | - | Devuelve la lista de categorías disponibles en la tienda. |

---

## Diagrama de Arquitectura

```mermaid
flowchart LR
    subgraph Frontend["Frontend (Vanilla JS)"]
        A[Usuario] --> B["POST /api/chat"]
    end

    subgraph Backend["Backend (Symfony 8)"]
        C[ProductChatController] --> D[ProductChatService]
        D -->|"Historial de sesión"| D
        D --> E[AgentInterface]
    end

    subgraph Tools["Herramientas"]
        F[ProductSearchTool]
        G[ProductPriceTool]
        H[ProductCategoryTool]
        I[ProductRepository]
        F & G & H --> I
    end

    B --> C
    E -->|tool call| F & G & H
    E <-->|chat/completions| Groq["Groq API / Llama 3.3"]
    E -->|respuesta| C --> A
```

---

## Licencia

MIT
