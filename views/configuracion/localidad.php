<?php require __DIR__ . '/../layout/navbar.php'; ?>

<div class="container" style="max-width:980px;">
  <h2 class="mt-4 mb-3">Configuración » Localidad</h2>

  <!-- Alta -->
  <form action="index.php?controller=config&action=localidad_store" method="POST" class="row g-2 mb-3">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

    <div class="col-md-6">
      <input class="form-control" name="nombre_localidad" placeholder="Nueva localidad" required>
    </div>

    <div class="col-md-4">
      <select class="form-select" name="id_provincia">
        <option value="">— Provincia —</option>
        <?php foreach ($provincias as $p): ?>
          <option value="<?= (int)$p['id_provincia'] ?>">
            <?= htmlspecialchars($p['nombre_provincia']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="col-md-2 d-grid">
      <button class="btn btn-primary">Añadir</button>
    </div>
  </form>

  <!-- Listado -->
  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th style="width:90px;">ID</th>
              <th>Localidad</th>
              <th>Provincia</th>
              <th class="text-end" style="width:160px;">Acciones</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td><?= (int)$it['id_localidad'] ?></td>
              <td><?= htmlspecialchars($it['nombre_localidad']) ?></td>
              <td><?= htmlspecialchars($it['nombre_provincia'] ?? '—') ?></td>
              <td class="text-end">
                <button class="btn btn-sm btn-outline-secondary me-1"
                        onclick="editarLocalidad(
                          <?= (int)$it['id_localidad'] ?>,
                          '<?= htmlspecialchars($it['nombre_localidad'], ENT_QUOTES) ?>',
                          '<?= isset($it['id_provincia']) ? (int)$it['id_provincia'] : '' ?>'
                        )">
                  <i class="bi bi-pencil"></i>
                </button>
                <form action="index.php?controller=config&action=localidad_delete" method="POST"
                      class="d-inline" onsubmit="return confirmarBorrar(this)">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                  <input type="hidden" name="id_localidad" value="<?= (int)$it['id_localidad'] ?>">
                  <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal edición -->
<div class="modal" tabindex="-1" id="modalEditarLocalidad">
  <div class="modal-dialog"><div class="modal-content">
    <div class="modal-header">
      <h5 class="modal-title">Editar localidad</h5>
      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <form action="index.php?controller=config&action=localidad_update" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
      <input type="hidden" name="id_localidad" id="edit_id_localidad">
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Nombre</label>
          <input class="form-control" id="edit_nombre_localidad" name="nombre_localidad" required>
        </div>
        <div>
          <label class="form-label">Provincia</label>
          <select class="form-select" id="edit_id_provincia" name="id_provincia">
            <option value="">— Provincia —</option>
            <?php foreach ($provincias as $p): ?>
              <option value="<?= (int)$p['id_provincia'] ?>">
                <?= htmlspecialchars($p['nombre_provincia']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div></div>
</div>

<script>
function confirmarBorrar(form){
  if (typeof mostrarPregunta === 'function') {
    mostrarPregunta('¿Eliminar?', 'Esta acción no se puede deshacer.', 'Sí, eliminar', 'Cancelar', () => form.submit());
    return false;
  }
  return confirm('¿Eliminar registro?');
}
function editarLocalidad(id, nombre, id_provincia){
  document.getElementById('edit_id_localidad').value = id;
  document.getElementById('edit_nombre_localidad').value = nombre;
  const sel = document.getElementById('edit_id_provincia');
  if (id_provincia !== '' && sel) sel.value = id_provincia;
  new bootstrap.Modal(document.getElementById('modalEditarLocalidad')).show();
}
</script>

<?php require __DIR__ . '/../layout/footer.php'; ?>
