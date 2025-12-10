<?php
// frontend/templates/address_form.php
// Variables esperadas: $d (array opcional con valores por defecto)
$d = $datos_dir ?? [];
?>

<div class="form-group">
    <label>Dirección completa</label>
    <input type="text" name="direccion" id="direccion" class="form-input" 
           placeholder="Calle, número, piso, puerta..."
           value="<?= htmlspecialchars($d['direccion'] ?? '') ?>" required>
</div>

<div class="form-row">
    <div class="form-group">
        <label>Ciudad</label>
        <input type="text" name="ciudad" id="ciudad" class="form-input" 
               value="<?= htmlspecialchars($d['ciudad'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label>Provincia/Estado</label>
        <input type="text" name="provincia" id="provincia" class="form-input" 
               value="<?= htmlspecialchars($d['provincia'] ?? '') ?>" required>
    </div>
    <div class="form-group">
        <label>Código Postal</label>
        <input type="text" name="codigo_postal" id="codigo_postal" class="form-input" 
               value="<?= htmlspecialchars($d['codigo_postal'] ?? '') ?>" required>
    </div>
</div>

<div class="form-group">
    <label>País</label>
    <select name="pais" id="pais" class="form-input" required>
        <option value="España" <?= (isset($d['pais']) && $d['pais'] == 'España') ? 'selected' : '' ?>>España</option>
        <option value="Portugal" <?= (isset($d['pais']) && $d['pais'] == 'Portugal') ? 'selected' : '' ?>>Portugal</option>
        <option value="Francia" <?= (isset($d['pais']) && $d['pais'] == 'Francia') ? 'selected' : '' ?>>Francia</option>
    </select>
</div>