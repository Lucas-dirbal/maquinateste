# Setup (PHP + MySQL + API Node)

## 1) Banco de Dados (MySQL)
1. Crie o banco e tabelas:
   ```sql
   SOURCE schema.sql;
   ```
   ou rode o conteúdo de `schema.sql` no seu MySQL.

> Credenciais padrão usadas:
> - host: `localhost`
> - user: `root`
> - pass: `` (vazio)
> - db: `tcc_equipamentos`

Você pode mudar isso:
- No PHP: edite `api/conn.php` **ou** defina variáveis de ambiente `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`.
- No Node: defina as variáveis `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME` antes de iniciar.

## 2) Backend PHP (endpoints prontos)
- `api/login.php`
- `api/usuarios.php`  (GET, POST, PUT, DELETE)
- `api/reservas.php`  (GET, POST, PUT)

Páginas novas:
- `admin.php`        → gestão de usuários
- `pedagogico.php`   → gestão de reservas (aprovar/rejeitar)

Para rodar rapidamente o PHP embutido:
```bash
php -S localhost:8000 -t .
```
Acesse: `http://localhost:8000/admin.php` e `http://localhost:8000/pedagogico.php`

## 3) API Node (MySQL)
Dentro de `node-api/`:
```bash
cd node-api
npm install
# (Opcional) exporte variáveis de ambiente:
# set DB_HOST=localhost (Windows) | export DB_HOST=localhost (Linux/Mac)
npm start
```
A API sobe em `http://localhost:4000` com rotas:
- `POST /api/login`
- `GET/POST /api/usuarios`, `PUT/DELETE /api/usuarios/:id`
- `GET/POST /api/reservas`, `PUT /api/reservas/:id`

## 4) Redirecionamento por função (papéis)
Depois do login (via `api/login.php` ou `/api/login` do Node), redirecione pelo campo `tipo`:
- `admin`        → `/admin.php`
- `pedagogico`   → `/pedagogico.php`
- `estudante`    → sua tela atual (ex.: `tala_aluno.html`)

Exemplo no front:
```js
// resposta = { success:true, user:{ tipo:'admin' } }
if(resposta.success){
  const t = resposta.user.tipo;
  if(t === 'admin') location.href = '/admin.php';
  else if(t === 'pedagogico') location.href = '/pedagogico.php';
  else location.href = '/tala_aluno.html';
}
```

## 5) Observações
- Removi dependência de SQLite do fluxo principal e mantive **MySQL** como fonte única de dados.
- Tanto o **PHP** quanto a **API Node** usam o **mesmo banco**. Você pode usar só um, ou ambos.
- Ajuste CORS conforme necessário na API Node.
