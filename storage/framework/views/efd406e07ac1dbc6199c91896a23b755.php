<?php $__env->startSection('title', 'Carro'); ?>

<?php $__env->startSection('content'); ?>
<div class="between">
    <h1>Carro total de la tienda</h1>
    <a class="btn btn-secondary" href="<?php echo e(route('tienda.catalogo')); ?>">Seguir comprando</a>
</div>

<?php if($planExtra): ?>
    <div class="alert">
        Extras asociados al pedido mensual de <?php echo e($planExtra->producto?->nombre); ?>. La confirmacion se hace en el pago.
    </div>
<?php endif; ?>

<?php if($items->isEmpty()): ?>
    <div class="card">El carro esta vacio.</div>
<?php else: ?>
<form method="POST" action="<?php echo e(route('tienda.carro.actualizar')); ?>">
    <?php echo csrf_field(); ?>
    <div class="card">
        <table>
            <thead><tr><th>Producto</th><th>Seccion</th><th>Precio</th><th>Cantidad</th><th>Total</th><th>Eliminar</th></tr></thead>
            <tbody>
                <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><strong><?php echo e($item['producto']->nombre); ?></strong><br><span class="muted"><?php echo e($item['producto']->marca); ?></span></td>
                        <td><span class="badge"><?php echo e($item['producto']->categoria); ?></span></td>
                        <td>$<?php echo e(number_format($item['producto']->precio, 0, ',', '.')); ?></td>
                        <td><input class="form-control form-control-sm" type="number" min="0" name="cantidades[<?php echo e($item['producto']->id); ?>]" value="<?php echo e($item['cantidad']); ?>"></td>
                        <td>$<?php echo e(number_format($item['total'], 0, ',', '.')); ?></td>
                        <td><button class="btn-secondary" type="button" onclick="this.closest('tr').querySelector('input').value=0;this.form.submit()">Eliminar</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <div style="text-align:right;margin-top:16px">
            <p>Subtotal: <strong>$<?php echo e(number_format($subtotal, 0, ',', '.')); ?></strong></p>
            <p>Envio: <strong>$<?php echo e(number_format($costoEnvio, 0, ',', '.')); ?></strong></p>
            <h2>Total: $<?php echo e(number_format($total, 0, ',', '.')); ?></h2>
            <div class="row" style="justify-content:flex-end">
                <button class="btn-secondary">Actualizar</button>
                <a class="btn btn-success" href="<?php echo e(route('tienda.checkout')); ?>">Pagar todo</a>
            </div>
        </div>
    </div>
</form>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\wamp64\www\veterfood\resources\views/tienda/carro.blade.php ENDPATH**/ ?>