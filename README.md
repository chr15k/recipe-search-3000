# Recipe Search 3000

> [!NOTE]
> This repo is based on [wildalaskan/skeleton-app-vue](https://github.com/wildalaskan/skeleton-app-vue) with the addition of TailwindCSS

## Quick Setup Guide

### Prerequisites

-   Docker
-   Docker Compose

### Installation Steps

1. **Clone the repository**

    ```bash
    git clone git@github.com:chr15k/recipe-search-3000.git
    cd recipe-search-3000
    ```

2. **Install dependencies**

    ```bash
    docker run --rm \
         --pull=always \
         -v "$(pwd)":/opt \
         -w /opt \
         laravelsail/php82-composer:latest \
         bash -c "composer install"
    ```

3. **Configure environment**

    ```bash
    cp .env.example .env
    ./vendor/bin/sail up -d
    ./vendor/bin/sail art key:generate
    ```

4. **Set up database**

    ```bash
    ./vendor/bin/sail art migrate --seed
    ```

    This seeds the database with 100,000 recipes, each having 4-6 ingredients.

5. **Start the frontend**
    ```bash
    ./vendor/bin/sail npm install --prefix frontend
    ./vendor/bin/sail npm run dev --prefix frontend
    ```

### Accessing the Application

-   **Frontend**: [http://localhost:3000](http://localhost:3000)
-   **Backend**: [http://localhost:8888](http://localhost:8888)

## API Documentation

### Recipes

-   **Search Recipes**

    -   `GET /api/recipes/search`
    -   Parameters:
        -   `author_email` (exact match)
        -   `keyword` (fulltext search across name, description, ingredients, or steps)
        -   `ingredient` (fulltext search for ingredient)

-   **Get Recipe**
    -   `GET /api/recipes/{slug}`
    -   Retrieves a specific recipe by slug

> [!NOTE]
> Search caching can be configured in your `.env` file:
>
> -   `SEARCH_CACHE_ENABLED`: Set to `false` to disable caching
> -   `SEARCH_CACHE_DURATION_MINUTES`: Set the cache duration
>
> After changing cache settings, run `./vendor/bin/sail art cache:clear`

## Development Tools

### Database Access

-   **Via terminal**:
    ```bash
    docker exec -it laravel-mysql-1 bash -c "mysql -uroot -ppassword"
    ```
-   **Via GUI**: Connect to 127.0.0.1 port 3333

### Useful Commands

-   Stop all containers:

    ```bash
    ./vendor/bin/sail down
    ```

-   Reset database:
    ```bash
    ./vendor/bin/sail art db:wipe && ./vendor/bin/sail art migrate --seed
    ```

## Running Tests

```bash
./vendor/bin/sail art test
```
