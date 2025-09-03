<?php
// Página simples de administração (lista usuários e permite alterar tipo/excluir)
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin - Controle de Equipamentos</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Admin</h1>
    <div class="bg-white p-4 rounded shadow">
      <div class="flex gap-2 mb-4">
        <input id="nome" class="border p-2 flex-1" placeholder="Nome">
        <input id="email" class="border p-2 flex-1" placeholder="Email">
        <input id="senha" type="password" class="border p-2" placeholder="Senha">
        <select id="tipo" class="border p-2">
          <option value="estudante">estudante</option>
          <option value="pedagogico">pedagogico</option>
          <option value="admin">admin</option>
        </select>
        <button id="btnAdd" class="px-3 py-2 bg-blue-600 text-white rounded">Adicionar</button>
      </div>
      <table class="w-full text-sm">
        <thead><tr class="text-left border-b"><th>Nome</th><th>Email</th><th>Tipo</th><th>Ações</th></tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
  </div>
<script>
const api = (path, opt={}) => fetch('/api/'+path, {headers:{'Content-Type':'application/json'}, ...opt}).then(r=>r.json());
const tbody = document.getElementById('tbody');
function load(){
  fetch('/api/usuarios.php').then(r=>r.json()).then(rows=>{
    tbody.innerHTML = rows.map(u => \`
      <tr class="border-b">
        <td class="py-2">\${u.nome}</td>
        <td>\${u.email}</td>
        <td>
          <select data-id="\${u.id}" class="border p-1 tipoSel">
            <option \${u.tipo==='estudante'?'selected':''}>estudante</option>
            <option \${u.tipo==='pedagogico'?'selected':''}>pedagogico</option>
            <option \${u.tipo==='admin'?'selected':''}>admin</option>
          </select>
        </td>
        <td>
          <button data-id="\${u.id}" class="del px-2 py-1 bg-red-600 text-white rounded">Excluir</button>
        </td>
      </tr>\`).join('');
    document.querySelectorAll('.tipoSel').forEach(s=>s.addEventListener('change', e=>{
      const id = e.target.dataset.id;
      api('usuarios.php', {method:'PUT', body: JSON.stringify({id, tipo:e.target.value})}).then(load);
    }));
    document.querySelectorAll('.del').forEach(b=>b.addEventListener('click', e=>{
      const id = e.target.dataset.id;
      fetch('/api/usuarios.php?id='+id, {method:'DELETE'}).then(()=>load());
    }));
  });
}
document.getElementById('btnAdd').onclick = ()=>{
  const nome = document.getElementById('nome').value.trim();
  const email = document.getElementById('email').value.trim();
  const senha = document.getElementById('senha').value;
  const tipo = document.getElementById('tipo').value;
  if(!nome||!email||!senha) return alert('Preencha todos os campos');
  api('usuarios.php', {method:'POST', body: JSON.stringify({nome,email,senha,tipo})}).then(load);
};
load();
</script>
</body>
</html>
