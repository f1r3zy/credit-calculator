# Credit Calculator

Simulator complet pentru credite bancare.

## Cerințe de sistem
- PHP 8.2+
- MySQL/MariaDB
- Composer

## Instalare
1. Clonați repository-ul.
2. Copiați `.env.example` în `.env` și configurați datele de conectare la baza de date.
3. Rulați `composer install`.
4. Importați `dump.sql` în MySQL.
5. Porniți serverul: `php -S localhost:8000 -t public/`.
6. Accesați `http://localhost:8000`.

## Endpoint-uri API
- `POST /api/calculate` - calculează un credit. Parametri (JSON): amount, months, interest_type, rate, method, etc.
- `POST /api/early-repayment` - rambursare anticipată.
- `GET /api/reference-rates/latest` - ultima rată de referință.
- `POST /api/simulations` - salvează o simulare (autentificat).
- `GET /api/simulations` - lista simulărilor.
- `GET /api/simulations/{id}` - detalii simulare.
...