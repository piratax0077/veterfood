<?php $__env->startSection('title', 'Tienda'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .store-header{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:start}
    .store-header h1{font-size:32px;margin-bottom:8px}
    .category-tabs{display:flex;gap:10px;flex-wrap:wrap;margin:16px 0 28px}
    .category-tabs .btn{background:#e5e7eb;color:#111827}
    .category-tabs .active{background:#d1fae5;color:#14532d;box-shadow:inset 0 0 0 2px #166534}
    .store-filters{display:grid;grid-template-columns:minmax(220px,1.25fr) minmax(200px,1fr) minmax(190px,1fr) auto;gap:14px;align-items:end;margin:0 0 34px;padding:18px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 8px 24px rgba(15,23,42,.07)}
    .filter-field{display:flex;flex-direction:column;gap:7px;min-width:0}
    .filter-field label{margin:0;color:#475569;font-size:13px;font-weight:700}
    .filter-control-wrap{position:relative}
    .filter-control-wrap.search-control:before{content:"\1F50D";position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;opacity:.58;pointer-events:none}
    .filter-field input,.filter-field select{width:100%;height:44px;border:1px solid #cbd5e1;border-radius:9px;padding:8px 12px;background:#fff;color:#0f172a;outline:none;transition:border-color .18s,box-shadow .18s}
    .filter-field input{padding-left:39px}
    .filter-field input:focus,.filter-field select:focus{border-color:#14b8a6;box-shadow:0 0 0 3px rgba(20,184,166,.14)}
    .filter-actions{display:flex;gap:9px;align-items:center;min-height:44px}
    .filter-actions button,.filter-actions .btn{height:44px;display:inline-flex;align-items:center;justify-content:center;white-space:nowrap;border-radius:9px;padding:0 22px}
    .product-photo{height:148px;border-radius:8px;margin:-4px -4px 14px;display:flex;align-items:center;justify-content:center;perspective:720px;overflow:hidden;background:linear-gradient(135deg,#eff6ff,#f8fafc 48%,#dcfce7)}
    .product-photo img{width:100%;height:100%;object-fit:cover}
    .product-3d{width:92px;height:108px;border-radius:16px 16px 12px 12px;transform:rotateX(12deg) rotateY(-22deg);box-shadow:24px 28px 36px rgba(15,23,42,.18);position:relative;background:linear-gradient(145deg,var(--visual-a),var(--visual-b))}
    .product-3d:before{content:"";position:absolute;inset:10px 12px auto;height:38px;border-radius:12px;background:rgba(255,255,255,.75)}
    .product-3d:after{content:attr(data-label);position:absolute;left:10px;right:10px;bottom:14px;color:white;font-weight:800;text-align:center;font-size:12px;text-shadow:0 1px 4px rgba(0,0,0,.28)}
    .product-shadow{width:120px;height:22px;background:rgba(15,23,42,.16);filter:blur(8px);border-radius:999px;position:absolute;transform:translateY(58px)}
    .visual-alimento_mascota{--visual-a:#16a34a;--visual-b:#854d0e}
    .visual-medicamento{--visual-a:#0ea5e9;--visual-b:#0369a1}
    .visual-juguete{--visual-a:#f97316;--visual-b:#be123c}
    .visual-utensilio{--visual-a:#64748b;--visual-b:#0f766e}
    .visual-cuidado{--visual-a:#14b8a6;--visual-b:#7c3aed}
    .visual-hotel{--visual-a:#a16207;--visual-b:#7f1d1d}
    .visual-paseo_diario{--visual-a:#22c55e;--visual-b:#0284c7}
    .visual-cementerio{--visual-a:#475569;--visual-b:#334155}
    .visual-servicio{--visual-a:#2563eb;--visual-b:#9333ea}
    @media(max-width:1050px){.store-filters{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-actions{grid-column:1/-1}}
    @media(max-width:900px){.store-header{grid-template-columns:1fr}}
    @media(max-width:620px){.store-filters{grid-template-columns:1fr;padding:14px}.filter-actions{grid-column:auto}.filter-actions button,.filter-actions .btn{flex:1}.category-tabs{margin-bottom:18px}}
</style>

<?php
    $titulosCategoria = [
        'alimento_mascota' => ['titulo' => 'Alimentos', 'descripcion' => 'Alimentos, snacks, productos de rutina y compras rapidas para el hogar.'],
        'medicamento' => ['titulo' => 'Farmacia', 'descripcion' => 'Medicamentos, antiparasitarios, suplementos y apoyo sanitario.'],
        'juguete' => ['titulo' => 'Juguetes', 'descripcion' => 'Juguetes, mordedores, enrichment y accesorios para actividad diaria.'],
        'hotel' => ['titulo' => 'Hoteles', 'descripcion' => 'Reservas, estadias diarias y convenios de hoteleria para mascotas.'],
        'paseo_diario' => ['titulo' => 'Paseos diarios', 'descripcion' => 'Paseos programados, visitas y acompanamiento diario para mascotas.'],
        'cementerio' => ['titulo' => 'Cementerio', 'descripcion' => 'Servicios de despedida, retiro y apoyo respetuoso para mascotas.'],
        'cuidado' => ['titulo' => 'Cuidados y utiles', 'descripcion' => 'Higiene, limpieza, paseo, transporte y articulos utiles para mascotas.'],
        'servicio' => ['titulo' => 'Servicios a domicilio', 'descripcion' => 'Bano, peluqueria, veterinaria a domicilio y apoyos programables.'],
        'utensilio' => ['titulo' => 'Utiles', 'descripcion' => 'Camas, platos, correas, transporte y articulos utiles para mascotas.'],
    ];
    $cabecera = $titulosCategoria[$categoria] ?? ($secciones[$categoria] ?? ['titulo' => 'Tienda para mascotas', 'descripcion' => 'Venta online, carro, pago local, servicios y despacho con tracking.']);
?>

<div class="store-header">
    <div>
        <h1><?php echo e($cabecera['titulo']); ?></h1>
        <p class="muted">
            <?php echo e($cabecera['descripcion']); ?>

        </p>
    </div>
    <div class="row">
        <a class="btn" href="<?php echo e(route('tienda.carro')); ?>">Carro total (<?php echo e(array_sum($carro)); ?>)</a>
        <?php if(array_sum($carro) > 0): ?>
            <a class="btn btn-success" href="<?php echo e(route('tienda.checkout')); ?>">Pagar todo</a>
        <?php endif; ?>
    </div>
</div>

<?php if($planExtra): ?>
    <div class="alert">
        Estas agregando productos para complementar tu pedido mensual de <?php echo e($planExtra->producto?->nombre); ?>.
        En el pago podras indicar si quieres incluirlos tambien en tu pedido mensual.
    </div>
<?php endif; ?>

<div class="category-tabs">
    <a class="btn <?php echo e(!$categoria || $categoria === 'general' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo')); ?>">Todo</a>
    <a class="btn <?php echo e($categoria === 'alimento_mascota' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'alimento_mascota'])); ?>">Alimentos</a>
    <a class="btn <?php echo e($categoria === 'medicamento' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'medicamento'])); ?>">Farmacia</a>
    <a class="btn <?php echo e($categoria === 'juguete' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'juguete'])); ?>">Juguetes</a>
    <a class="btn <?php echo e($categoria === 'hotel' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'hotel'])); ?>">Hoteles</a>
    <a class="btn <?php echo e($categoria === 'paseo_diario' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'paseo_diario'])); ?>">Paseos diarios</a>
    <a class="btn <?php echo e($categoria === 'cementerio' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'cementerio'])); ?>">Cementerio</a>
    <a class="btn <?php echo e($categoria === 'cuidado' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'cuidado'])); ?>">Cuidados</a>
    <a class="btn <?php echo e($categoria === 'servicio' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'servicio'])); ?>">Servicios</a>
    <a class="btn <?php echo e($categoria === 'utensilio' ? 'active' : ''); ?>" href="<?php echo e(route('tienda.catalogo', ['categoria' => 'utensilio'])); ?>">Utiles</a>
</div>

<form method="GET" action="<?php echo e(route('tienda.catalogo')); ?>" class="store-filters">
    <?php if($categoria): ?>
        <input type="hidden" name="categoria" value="<?php echo e($categoria); ?>">
    <?php endif; ?>
    <div class="filter-field">
        <label for="buscar">Buscar por nombre</label>
        <div class="filter-control-wrap search-control">
            <input id="buscar" type="search" name="buscar" value="<?php echo e($busqueda); ?>" placeholder="Nombre del producto o servicio" aria-label="Buscar por nombre">
        </div>
    </div>
    <div class="filter-field">
        <label for="tipo">Categoría</label>
        <div class="filter-control-wrap">
        <select id="tipo" name="tipo" aria-label="Buscar por categoría">
            <option value="">Todas</option>
            <?php $__currentLoopData = $categoriasTienda; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slug => $titulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($slug); ?>" <?php if($filtroCategoria === $slug): echo 'selected'; endif; ?>><?php echo e($titulo); ?></option>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </select>
        </div>
    </div>
    <div class="filter-field">
        <label for="orden">Ordenar por precio</label>
        <div class="filter-control-wrap">
        <select id="orden" name="orden" aria-label="Ordenar por precio">
            <option value="">Normal</option>
            <option value="precio_asc" <?php if($orden === 'precio_asc'): echo 'selected'; endif; ?>>Menor a mayor</option>
            <option value="precio_desc" <?php if($orden === 'precio_desc'): echo 'selected'; endif; ?>>Mayor a menor</option>
        </select>
        </div>
    </div>
    <div class="filter-actions">
        <button class="btn-success">Aplicar</button>
        <?php if($busqueda || $filtroCategoria || $orden): ?>
            <a class="btn btn-secondary" href="<?php echo e(route('tienda.catalogo', $categoria ? ['categoria' => $categoria] : [])); ?>">Limpiar</a>
        <?php endif; ?>
    </div>
</form>

<div class="grid">
<?php $__empty_1 = true; $__currentLoopData = $productos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $producto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="col-3">
        <div class="card">
            <div class="product-photo">
                <?php if($producto->foto_url): ?>
                    <img src="<?php echo e(asset($producto->foto_url)); ?>" alt="<?php echo e($producto->nombre); ?>">
                <?php else: ?>
                    <div class="product-shadow"></div>
                    <div class="product-3d visual-<?php echo e($producto->categoria); ?>" data-label="<?php echo e(strtoupper(substr($producto->categoria, 0, 3))); ?>"></div>
                <?php endif; ?>
            </div>
            <span class="badge"><?php echo e($producto->categoria); ?></span>
            <h3 style="margin-top:12px"><?php echo e($producto->nombre); ?></h3>
            <p class="muted"><?php echo e($producto->marca); ?> <?php echo e($producto->peso ? '- '.$producto->peso : ''); ?></p>
            <p><?php echo e($producto->descripcion); ?></p>
            <div class="between">
                <strong>$<?php echo e(number_format($producto->precio, 0, ',', '.')); ?></strong>
                <span class="muted">Stock <?php echo e($producto->stock); ?></span>
            </div>
            <form method="POST" action="<?php echo e(route('tienda.agregar', $producto)); ?>" class="row" style="margin-top:14px">
                <?php echo csrf_field(); ?>
                <input class="form-control form-control-sm" type="number" name="cantidad" value="1" min="1" max="<?php echo e(max(1, $producto->stock)); ?>" style="max-width:90px">
                <button class="btn-success">Agregar</button>
            </form>
        </div>
    </div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
    <div class="col-12"><div class="card">No hay productos activos.</div></div>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\veterfood\resources\views/tienda/catalogo.blade.php ENDPATH**/ ?>