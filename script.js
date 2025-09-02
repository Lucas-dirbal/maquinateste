const express = require('express');
const sqlite3 = require('sqlite3').verbose();
const bodyParser = require('body-parser');
const bcrypt = require('bcrypt');

const app = express();
const PORT = 3000;

// Configurar para receber JSON
app.use(bodyParser.json());

// Criar banco de dados SQLite
const db = new sqlite3.Database('logins.db', (err) => {
    if (err) return console.error(err.message);
    console.log('Conectado ao banco SQLite.');
});

// Criar tabela de usuários, se não existir
db.run(`
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE,
        password TEXT
    )
`);

// Função para cadastrar usuário
app.post('/register', async (req, res) => {
    const { username, password } = req.body;
    if (!username || !password) return res.status(400).send('Informe username e password.');

    try {
        // Criptografar a senha
        const hashedPassword = await bcrypt.hash(password, 10);

        const query = `INSERT INTO users (username, password) VALUES (?, ?)`;
        db.run(query, [username, hashedPassword], function(err) {
            if (err) {
                if (err.message.includes('UNIQUE')) return res.status(400).send('Usuário já existe.');
                return res.status(500).send(err.message);
            }
            res.send(`Usuário cadastrado com sucesso! ID: ${this.lastID}`);
        });
    } catch (error) {
        res.status(500).send(error.message);
    }
});

// Função para login
app.post('/login', (req, res) => {
    const { username, password } = req.body;
    if (!username || !password) return res.status(400).send('Informe username e password.');

    db.get(`SELECT * FROM users WHERE username = ?`, [username], async (err, user) => {
        if (err) return res.status(500).send(err.message);
        if (!user) return res.status(400).send('Usuário não encontrado.');

        // Comparar a senha com o hash
        const match = await bcrypt.compare(password, user.password);
        if (!match) return res.status(400).send('Senha incorreta.');

        res.send(`Login bem-sucedido! Bem-vindo, ${user.username}`);
    });
});

// Rota para listar usuários (apenas para teste)
app.get('/users', (req, res) => {
    db.all(`SELECT id, username FROM users`, [], (err, rows) => {
        if (err) return res.status(500).send(err.message);
        res.json(rows);
    });
});

// Iniciar servidor
app.listen(PORT, () => {
    console.log(`Servidor rodando em http://localhost:${PORT} Use /register ou /login para testar.`);
});
