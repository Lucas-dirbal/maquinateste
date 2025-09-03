<?php
// Página da equipe pedagógica (lista reservas e permite aprovar/rejeitar)
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Equipe Pedagógica - Controle de Equipamentos</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.1/dist/tailwind.min.css">
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-5xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Equipe Pedagógica</h1>
    <div class="bg-white p-4 rounded shadow">
      <table class="w-full text-sm">
        <thead><tr class="text-left border-b">
          <th>ID</th><th>Equipamento</th><th>Usuário</th><th>Retirada</th><th>Devolução</th><th>Status</th><th>Ações</th>
        </tr></thead>
        <tbody id="tbody"></tbody>
      </table>
    </div>
  </div>
<script>
function load(){
  fetch('/api/reservas.php').then(r=>r.json()).then(rows=>{
    const tbody = document.getElementById('tbody');
    tbody.innerHTML = rows.map(r => \`
      <tr class="border-b">
        <td class="py-2">\${r.id}</td>
        <td>\${r.equipamento_nome || r.equipamento_id}</td>
        <td>\${r.usuario_nome || r.usuario_id}</td>
        <td>\${r.data_retirada}</td>
        <td>\${r.data_devolucao}</td>
        <td><span class="px-2 py-1 rounded bg-gray-200">\${r.status}</span></td>
        <td class="space-x-2">
          <button data-id="\${r.id}" data-st="approved" class="act px-2 py-1 bg-green-600 text-white rounded">Aprovar</button>
          <button data-id="\${r.id}" data-st="rejected" class="act px-2 py-1 bg-red-600 text-white rounded">Rejeitar</button>
        </td>
      </tr>\`).join('');
    document.querySelectorAll('.act').forEach(b=>b.addEventListener('click', e=>{
      const id = e.target.getAttribute('data-id');
      const status = e.target.getAttribute('data-st');
      fetch('/api/reservas.php', {method:'PUT', headers:{'Content-Type':'application/json'}, body: JSON.stringify({id, status})})
        .then(r=>r.json()).then(load);
    }));
  });
}
load();
</script>
</body>
</html>
