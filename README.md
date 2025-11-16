# clash-of-cultures

## Frontend setup(for development)
```
npm install
```

## Backend setup(for development)
install PHP 8+

## Database setup
1. Create MySQL database
2. Update connection settings in `api/.env.local` file:
DB_HOST=localhost
DB_NAME=your_database_name
DB_USER=your_username
DB_PASSWORD=your_password

### Compiles and hot-reloads for development(front)
```
npm run serve
```
Frontend will run on `http://localhost:8080`

### Compiles for development(back)
```
cd api
php -S localhost:8081
```
Backend API will run on `http://localhost:8081`


### Compiles and minifies for production
```
npm run build
```

### Lints and fixes files
```
npm run lint
```

### Customize configuration
See [Configuration Reference](https://cli.vuejs.org/config/).
