<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<title>My Notes</title>
<style>
  * { box-sizing: border-box; margin:0; padding:0; -webkit-text-size-adjust:100%; }
  html, body { overflow-x: hidden; max-width:100%; overscroll-behavior-x: none; }
  body { font-family: Arial, sans-serif; background:#202124; color:#e8eaed; padding: 12px; min-height:100vh; }
  header { text-align:center; margin-bottom: 16px; }
  header h1 { font-weight:500; font-size:1.4rem; }
  .add-note { max-width: 600px; margin: 0 auto 20px; background:#2d2e30; border:1px solid #5f6368; border-radius:8px; padding:10px 14px; }
  .add-note input, .add-note textarea { width:100%; background:transparent; border:none; color:#e8eaed; font-size:0.95rem; outline:none; resize:none; font-family:inherit; }
  .add-note input { margin-bottom:6px; font-weight:bold; }
  .add-note .actions { display:none; justify-content:flex-end; margin-top:8px; }
  .add-note.active .actions { display:flex; }
  .add-note button { background:#8ab4f8; color:#202124; border:none; padding:7px 14px; border-radius:4px; cursor:pointer; font-weight:bold; font-size:0.9rem; }
  .add-note button.cancel { background:transparent; color:#e8eaed; margin-right:8px; }
  .notes-grid { column-count: 2; column-gap: 8px; max-width: 900px; margin: 0 auto; width:100%; }
  @media (min-width: 600px) { .notes-grid { column-count: 3; column-gap: 16px; } }
  @media (min-width: 900px) { .notes-grid { column-count: 4; } }
  .note-card { background:#2d2e30; border:1px solid #5f6368; border-radius:8px; padding:10px 12px; margin-bottom:8px; break-inside: avoid; cursor:pointer; position:relative; max-width:100%; }
  .note-card h3 { margin-bottom:4px; font-size:0.9rem; word-wrap:break-word; overflow-wrap:break-word; padding-right:16px; }
  .note-card p { font-size:0.8rem; white-space:pre-wrap; color:#bdc1c6; word-wrap:break-word; overflow-wrap:break-word; }
  .note-card .delete-btn { position:absolute; top:6px; right:6px; background:none; border:none; color:#9aa0a6; font-size:1rem; cursor:pointer; padding:2px 6px; }
  .overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); align-items:center; justify-content:center; z-index:100; padding:16px; }
  .overlay.active { display:flex; }
  .overlay-box { background:#2d2e30; border-radius:8px; padding:16px; width:100%; max-width:500px; }
  .overlay-box input, .overlay-box textarea { width:100%; background:transparent; border:none; color:#e8eaed; font-size:1.05rem; outline:none; resize:none; font-family:inherit; margin-bottom:10px; word-wrap:break-word; }
  .overlay-box textarea { min-height:150px; }
  .overlay-box .actions { display:flex; justify-content:space-between; margin-top:10px; }
  .overlay-box button { background:#8ab4f8; color:#202124; border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:bold; }
  .overlay-box button.close { background:transparent; color:#e8eaed; }
  .overlay-box button.delete { background:#f28b82; color:#202124; }
</style>
</head>
<body>
<header><h1>My Notes</h1></header>
<div class="add-note" id="addNote">
  <input type="text" id="newTitle" placeholder="Title">
  <textarea id="newContent" placeholder="Take a note..." rows="1"></textarea>
  <div class="actions">
    <button class="cancel" onclick="cancelAdd()">Cancel</button>
    <button onclick="saveNewNote()">Save</button>
  </div>
</div>
<div class="notes-grid" id="notesGrid"></div>
<div class="overlay" id="overlay">
  <div class="overlay-box">
    <input type="text" id="editTitle">
    <textarea id="editContent"></textarea>
    <div class="actions">
      <button class="delete" onclick="deleteNote()">Delete</button>
      <div>
        <button class="close" onclick="closeOverlay()">Close</button>
        <button onclick="saveEditNote()">Save</button>
      </div>
    </div>
  </div>
</div>
<script>
let currentEditId = null;
const addNote = document.getElementById('addNote');
document.getElementById('newTitle').addEventListener('focus', () => addNote.classList.add('active'));
document.getElementById('newContent').addEventListener('focus', () => addNote.classList.add('active'));

function cancelAdd() {
  document.getElementById('newTitle').value = '';
  document.getElementById('newContent').value = '';
  addNote.classList.remove('active');
}

function loadNotes() {
  fetch('api.php')
    .then(res => res.json())
    .then(notes => {
      const grid = document.getElementById('notesGrid');
      grid.innerHTML = '';
      notes.forEach(note => {
        const card = document.createElement('div');
        card.className = 'note-card';
        card.innerHTML = '<h3></h3><p></p><button class="delete-btn">x</button>';
        card.querySelector('h3').innerText = note.title;
        card.querySelector('p').innerText = note.content;
        card.querySelector('.delete-btn').onclick = (e) => { e.stopPropagation(); quickDelete(note.id); };
        card.onclick = () => openEdit(note);
        grid.appendChild(card);
      });
    });
}

function saveNewNote() {
  const title = document.getElementById('newTitle').value.trim();
  const content = document.getElementById('newContent').value.trim();
  if (!title && !content) { cancelAdd(); return; }
  const formData = new FormData();
  formData.append('action', 'create');
  formData.append('title', title);
  formData.append('content', content);
  fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(() => { cancelAdd(); loadNotes(); });
}

function openEdit(note) {
  currentEditId = note.id;
  document.getElementById('editTitle').value = note.title;
  document.getElementById('editContent').value = note.content;
  document.getElementById('overlay').classList.add('active');
}

function closeOverlay() {
  document.getElementById('overlay').classList.remove('active');
  currentEditId = null;
}

function saveEditNote() {
  const title = document.getElementById('editTitle').value.trim();
  const content = document.getElementById('editContent').value.trim();
  const formData = new FormData();
  formData.append('action', 'update');
  formData.append('id', currentEditId);
  formData.append('title', title);
  formData.append('content', content);
  fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(() => { closeOverlay(); loadNotes(); });
}

function deleteNote() {
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('id', currentEditId);
  fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(() => { closeOverlay(); loadNotes(); });
}

function quickDelete(id) {
  const formData = new FormData();
  formData.append('action', 'delete');
  formData.append('id', id);
  fetch('api.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(() => loadNotes());
}

loadNotes();
</script>
</body>
</html>
