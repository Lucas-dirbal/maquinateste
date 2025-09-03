const express = require('express');
const bodyParser = require('body-parser');
const mysql = require('mysql2/promise');
const cors = require('cors');

const PORT = process.env.NODE_PORT || 4000;
const DB_HOST = process.env.DB_HOST || 'localhost';
const DB_USER = process.env.DB_USER || 'root';
const DB_PASS = process.env.DB_PASS || '';
const DB_NAME = process.env.DB_NAME || 'tcc_equipamentos';

async function db(){
  return mysql.createPool({host:DB_HOST, user:DB_USER, password:DB_PASS, database:DB_NAME, connectionLimit: 5});
}
const app = express();
app.use(cors());
app.use(bodyParser.json());

// Login
app.post('/api/login', async (req, res) => {
  const {email, password} = req.body || {};
  if(!email || !password) return res.status(400).json({success:false, message:'Email e senha são obrigatórios'});
  const pool = await db();
  const [rows] = await pool.query('SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ? LIMIT 1', [email]);
  const user = rows[0];
  if(!user) return res.json({success:false, message:'Email ou senha incorretos'});
  const bcrypt = require('bcrypt');
  const ok = await bcrypt.compare(password, user.senha);
  if(!ok) return res.json({success:false, message:'Email ou senha incorretos'});
  res.json({success:true, user:{id:user.id, nome:user.nome, email:user.email, tipo:user.tipo}});
});

// CRUD usuarios
app.get('/api/usuarios', async (req, res) => {
  const pool = await db();
  const [rows] = await pool.query('SELECT id, nome, email, tipo, criado_em FROM usuarios ORDER BY id DESC');
  res.json(rows);
});
app.post('/api/usuarios', async (req, res) => {
  const {nome,email,senha,tipo} = req.body || {};
  if(!nome||!email||!senha||!tipo) return res.status(400).json({success:false, message:'Campos obrigatórios'});
  const bcrypt = require('bcrypt');
  const hash = await bcrypt.hash(senha, 10);
  const pool = await db();
  const [result] = await pool.query('INSERT INTO usuarios (nome,email,senha,tipo) VALUES (?,?,?,?)',[nome,email,hash,tipo]);
  res.json({success:true, id:result.insertId});
});
app.put('/api/usuarios/:id', async (req, res) => {
  const id = req.params.id;
  const {nome,email,senha,tipo} = req.body || {};
  const sets = []; const args = [];
  if(nome){ sets.push('nome=?'); args.push(nome); }
  if(email){ sets.push('email=?'); args.push(email); }
  if(tipo){ sets.push('tipo=?'); args.push(tipo); }
  if(senha){ const bcrypt=require('bcrypt'); const hash=await bcrypt.hash(senha,10); sets.push('senha=?'); args.push(hash); }
  if(!sets.length) return res.status(400).json({success:false, message:'Nada para atualizar'});
  args.push(id);
  const pool = await db();
  await pool.query(`UPDATE usuarios SET ${sets.join(', ')} WHERE id = ?`, args);
  res.json({success:true});
});
app.delete('/api/usuarios/:id', async (req, res) => {
  const pool = await db();
  await pool.query('DELETE FROM usuarios WHERE id = ?', [req.params.id]);
  res.json({success:true});
});

// Reservas
app.get('/api/reservas', async (req, res) => {
  const {usuario_id, status} = req.query;
  const pool = await db();
  if(usuario_id){
    const [rows] = await pool.query(
      `SELECT r.id, r.data_retirada, r.data_devolucao, r.motivo, r.status, e.nome AS equipamento_nome
       FROM reservas r JOIN equipamentos e ON r.equipamento_id = e.id WHERE r.usuario_id = ? ORDER BY r.id DESC`,
       [usuario_id]
    );
    return res.json(rows);
  }
  if(status){
    const [rows] = await pool.query(
      `SELECT r.*, u.nome AS usuario_nome, e.nome AS equipamento_nome
       FROM reservas r JOIN usuarios u ON r.usuario_id = u.id JOIN equipamentos e ON r.equipamento_id = e.id
       WHERE r.status = ? ORDER BY r.id DESC`, [status]);
    return res.json(rows);
  }
  const [rows] = await pool.query(
    `SELECT r.*, u.nome AS usuario_nome, e.nome AS equipamento_nome
     FROM reservas r JOIN usuarios u ON r.usuario_id = u.id JOIN equipamentos e ON r.equipamento_id = e.id
     ORDER BY r.id DESC`);
  res.json(rows);
});
app.post('/api/reservas', async (req, res) => {
  const {equipamento_id, usuario_id, data_retirada, data_devolucao, motivo, status} = req.body || {};
  if(!equipamento_id || !usuario_id || !data_retirada || !data_devolucao){
    return res.status(400).json({success:false, message:'Campos obrigatórios'});
  }
  const pool = await db();
  const [result] = await pool.query(
    'INSERT INTO reservas (equipamento_id, usuario_id, data_retirada, data_devolucao, motivo, status) VALUES (?,?,?,?,?,?)',
    [equipamento_id, usuario_id, data_retirada, data_devolucao, motivo || '', status || 'pending']
  );
  res.json({success:true, id: result.insertId});
});
app.put('/api/reservas/:id', async (req, res) => {
  const {status} = req.body || {};
  if(!status) return res.status(400).json({success:false, message:'Status é obrigatório'});
  const pool = await db();
  await pool.query('UPDATE reservas SET status=? WHERE id=?', [status, req.params.id]);
  res.json({success:true});
});

app.listen(PORT, () => console.log(`Node API rodando em http://localhost:${PORT}`));
