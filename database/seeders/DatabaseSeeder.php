<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\Bodega;
use App\Models\Cliente;
use App\Models\DireccionCliente;
use App\Models\Existencia;
use App\Models\LocalVenta;
use App\Models\Mascota;
use App\Models\PlanPedido;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $localCentral = LocalVenta::firstOrCreate(
            ['codigo' => 'LC-001'],
            [
                'nombre' => 'Local Central Santiago',
                'direccion' => 'Av. Distribucion 1000',
                'comuna' => 'Santiago',
                'telefono' => '+56220000000',
                'responsable' => 'Central Ventas',
                'activo' => true,
            ]
        );

        $localOriente = LocalVenta::firstOrCreate(
            ['codigo' => 'LC-002'],
            [
                'nombre' => 'Local Oriente',
                'direccion' => 'Av. Las Condes 5000',
                'comuna' => 'Las Condes',
                'telefono' => '+56221111111',
                'responsable' => 'Vendedor Oriente',
                'activo' => true,
            ]
        );

        $bodegaCentral = Bodega::firstOrCreate(
            ['codigo' => 'BD-001'],
            [
                'nombre' => 'Bodega Central',
                'direccion' => 'Centro logistico principal',
                'tipo' => 'central',
                'activo' => true,
            ]
        );

        $usuarios = [
            ['name' => 'Administrador Alimentos', 'email' => 'admin@alimentos.local', 'rol' => 'admin', 'local_venta_id' => null],
            ['name' => 'Central Ventas', 'email' => 'central@alimentos.local', 'rol' => 'central_ventas', 'local_venta_id' => $localCentral->id],
            ['name' => 'Vendedor Oriente', 'email' => 'vendedor@alimentos.local', 'rol' => 'vendedor', 'local_venta_id' => $localOriente->id],
            ['name' => 'Cliente Mascotas', 'email' => 'cliente@alimentos.local', 'rol' => 'cliente', 'local_venta_id' => $localCentral->id],
            ['name' => 'Cliente Semanal', 'email' => 'cliente.semanal@alimentos.local', 'rol' => 'cliente', 'local_venta_id' => $localOriente->id],
            ['name' => 'Repartidor Demo', 'email' => 'repartidor@alimentos.local', 'rol' => 'repartidor', 'local_venta_id' => null],
            ['name' => 'Contabilidad Alimentos', 'email' => 'contabilidad@alimentos.local', 'rol' => 'contabilidad', 'local_venta_id' => null],
        ];

        foreach ($usuarios as $usuario) {
            User::firstOrCreate(
                ['email' => $usuario['email']],
                [
                    'name' => $usuario['name'],
                    'password' => '12345678',
                    'rol' => $usuario['rol'],
                    'activo' => true,
                    'telefono' => '+56912345678',
                    'direccion' => 'Santiago Centro',
                    'local_venta_id' => $usuario['local_venta_id'],
                ]
            );
        }

        $productos = [
            ['nombre' => 'Adulto Pollo y Arroz', 'marca' => 'PetLife', 'peso' => '15 kg', 'precio' => 42990, 'stock' => 18],
            ['nombre' => 'Cachorro Premium', 'marca' => 'PetLife', 'peso' => '10 kg', 'precio' => 38990, 'stock' => 12],
            ['nombre' => 'Gato Indoor Salmon', 'marca' => 'MichiCare', 'peso' => '7.5 kg', 'precio' => 34990, 'stock' => 16],
            ['nombre' => 'Senior Razas Pequenas', 'marca' => 'VitalCan', 'peso' => '8 kg', 'precio' => 29990, 'stock' => 10],
        ];

        foreach ($productos as $producto) {
            Producto::firstOrCreate(
                ['nombre' => $producto['nombre'], 'marca' => $producto['marca']],
                array_merge($producto, [
                    'categoria' => 'alimento_mascota',
                    'descripcion' => 'Producto demo para venta local con despacho programado.',
                    'activo' => true,
                ])
            );
        }

        $adicionales = [
            ['nombre' => 'Antiparasitario mensual', 'marca' => 'VetCare', 'categoria' => 'medicamento', 'peso' => '1 dosis', 'precio' => 11990, 'stock' => 30],
            ['nombre' => 'Suplemento articular', 'marca' => 'MoviPet', 'categoria' => 'medicamento', 'peso' => '60 comp.', 'precio' => 15990, 'stock' => 20],
            ['nombre' => 'Pelota resistente', 'marca' => 'PlayPet', 'categoria' => 'juguete', 'peso' => null, 'precio' => 5990, 'stock' => 25],
            ['nombre' => 'Mordedor dental', 'marca' => 'PlayPet', 'categoria' => 'juguete', 'peso' => null, 'precio' => 7490, 'stock' => 22],
            ['nombre' => 'Plato acero inoxidable', 'marca' => 'HomePet', 'categoria' => 'utensilio', 'peso' => 'mediano', 'precio' => 8990, 'stock' => 18],
            ['nombre' => 'Dispensador de alimento', 'marca' => 'HomePet', 'categoria' => 'utensilio', 'peso' => '3 kg', 'precio' => 21990, 'stock' => 9],
            ['nombre' => 'Shampoo piel sensible', 'marca' => 'CleanPet', 'categoria' => 'cuidado', 'peso' => '500 ml', 'precio' => 9990, 'stock' => 18],
            ['nombre' => 'Toallitas higienicas', 'marca' => 'CleanPet', 'categoria' => 'cuidado', 'peso' => '80 un.', 'precio' => 6490, 'stock' => 26],
            ['nombre' => 'Correa reflectante', 'marca' => 'WalkPet', 'categoria' => 'utensilio', 'peso' => 'mediana', 'precio' => 12990, 'stock' => 14],
            ['nombre' => 'Hotel mascota dia completo', 'marca' => 'VetChile Hotel', 'categoria' => 'hotel', 'peso' => 'reserva', 'precio' => 24990, 'stock' => 12],
            ['nombre' => 'Hotel mascota noche', 'marca' => 'VetChile Hotel', 'categoria' => 'hotel', 'peso' => 'reserva', 'precio' => 39990, 'stock' => 8],
            ['nombre' => 'Paseo diario 30 minutos', 'marca' => 'VetChile Paseos', 'categoria' => 'paseo_diario', 'peso' => 'agenda', 'precio' => 8990, 'stock' => 30],
            ['nombre' => 'Paseo diario 60 minutos', 'marca' => 'VetChile Paseos', 'categoria' => 'paseo_diario', 'peso' => 'agenda', 'precio' => 14990, 'stock' => 20],
            ['nombre' => 'Retiro y despedida mascota', 'marca' => 'VetChile Cementerio', 'categoria' => 'cementerio', 'peso' => 'servicio', 'precio' => 59990, 'stock' => 8],
            ['nombre' => 'Ceremonia conmemorativa', 'marca' => 'VetChile Cementerio', 'categoria' => 'cementerio', 'peso' => 'servicio', 'precio' => 89990, 'stock' => 5],
            ['nombre' => 'Bano a domicilio', 'marca' => 'VetChile Movil', 'categoria' => 'servicio', 'peso' => 'agenda', 'precio' => 18990, 'stock' => 20],
            ['nombre' => 'Peluqueria a domicilio', 'marca' => 'VetChile Movil', 'categoria' => 'servicio', 'peso' => 'agenda', 'precio' => 29990, 'stock' => 12],
            ['nombre' => 'Veterinaria a domicilio', 'marca' => 'VetChile Movil', 'categoria' => 'servicio', 'peso' => 'consulta', 'precio' => 34990, 'stock' => 10],
        ];

        foreach ($adicionales as $producto) {
            Producto::firstOrCreate(
                ['nombre' => $producto['nombre'], 'marca' => $producto['marca']],
                array_merge($producto, [
                    'descripcion' => 'Producto adicional para sumar al carro de compras.',
                    'activo' => true,
                ])
            );
        }

        foreach (Producto::all() as $producto) {
            Existencia::updateOrCreate(
                ['producto_id' => $producto->id, 'bodega_id' => $bodegaCentral->id, 'local_venta_id' => null],
                [
                    'cantidad' => max(8, $producto->stock),
                    'stock_critico' => 6,
                    'stock_objetivo' => 25,
                ]
            );

            Existencia::updateOrCreate(
                ['producto_id' => $producto->id, 'bodega_id' => null, 'local_venta_id' => $localCentral->id],
                [
                    'cantidad' => 4,
                    'stock_critico' => 5,
                    'stock_objetivo' => 12,
                ]
            );
        }

        $cliente = User::where('email', 'cliente@alimentos.local')->first();
        $clienteSemanal = User::where('email', 'cliente.semanal@alimentos.local')->first();
        $productoMensual = Producto::where('nombre', 'Adulto Pollo y Arroz')->first();
        $productoSemanal = Producto::where('nombre', 'Gato Indoor Salmon')->first();

        foreach ([$cliente, $clienteSemanal] as $usuarioCliente) {
            if (!$usuarioCliente->cliente_id) {
                $registroCliente = Cliente::create([
                    'nombre' => $usuarioCliente->name,
                    'telefono' => $usuarioCliente->telefono,
                    'email' => $usuarioCliente->email,
                    'fecha_inscripcion' => now(),
                ]);

                $usuarioCliente->update(['cliente_id' => $registroCliente->id]);
                $usuarioCliente->refresh();
            }
        }

        DireccionCliente::firstOrCreate(
            ['user_id' => $cliente->id, 'alias' => 'Casa'],
            ['direccion' => $cliente->direccion ?: 'Santiago Centro', 'comuna' => 'Santiago', 'referencia' => 'Direccion principal', 'principal' => true]
        );

        DireccionCliente::firstOrCreate(
            ['user_id' => $clienteSemanal->id, 'alias' => 'Casa'],
            ['direccion' => 'Las Condes 1234', 'comuna' => 'Las Condes', 'referencia' => 'Entrega semanal', 'principal' => true]
        );

        $mascotaMensual = Mascota::firstOrCreate(
            ['cliente_id' => $cliente->cliente_id, 'nombre' => 'Max'],
            ['user_id' => $cliente->id, 'especie' => 'perro', 'raza' => 'Mestizo', 'peso_kg' => 18, 'observaciones' => 'Prefiere alimento adulto.']
        );

        $mascotaSemanal = Mascota::firstOrCreate(
            ['cliente_id' => $clienteSemanal->cliente_id, 'nombre' => 'Luna'],
            ['user_id' => $clienteSemanal->id, 'especie' => 'gato', 'raza' => 'Indoor', 'peso_kg' => 5, 'observaciones' => 'Entrega semanal.']
        );

        PlanPedido::firstOrCreate(
            ['user_id' => $cliente->id, 'producto_id' => $productoMensual->id, 'frecuencia' => 'mensual'],
            [
                'mascota_id' => $mascotaMensual->id,
                'cantidad' => 1,
                'proxima_entrega' => now()->addDays(5)->toDateString(),
                'direccion_entrega' => $cliente->direccion ?: 'Santiago Centro',
                'activo' => true,
            ]
        );

        PlanPedido::firstOrCreate(
            ['user_id' => $clienteSemanal->id, 'producto_id' => $productoSemanal->id, 'frecuencia' => 'semanal'],
            [
                'mascota_id' => $mascotaSemanal->id,
                'cantidad' => 1,
                'proxima_entrega' => now()->addDays(3)->toDateString(),
                'direccion_entrega' => 'Las Condes 1234',
                'activo' => true,
            ]
        );
    }
}
