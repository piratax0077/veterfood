<?php

// Datos de ejemplo de las ventanas para agendar hora (cita veterinaria de la tienda y servicios comprados).
// Cuando se conecte la agenda oficial, especies, razas y lugares saldrán del servidor.
return [
    'especies' => [
        'perro' => [
            'nombre' => 'Perro',
            'icono' => 'perro',
            'razas' => ['Mestizo', 'Beagle', 'Border Collie', 'Boxer', 'Bulldog Francés', 'Bulldog Inglés', 'Chihuahua', 'Cocker Spaniel', 'Dachshund (salchicha)', 'Golden Retriever', 'Husky Siberiano', 'Jack Russell Terrier', 'Labrador Retriever', 'Maltés', 'Pastor Alemán', 'Pomerania', 'Poodle', 'Pug', 'Rottweiler', 'Schnauzer', 'Shih Tzu', 'Yorkshire Terrier', 'Otra raza'],
        ],
        'gato' => [
            'nombre' => 'Gato',
            'icono' => 'gato',
            'razas' => ['Mestizo', 'Angora', 'Bengalí', 'British Shorthair', 'Maine Coon', 'Persa', 'Ragdoll', 'Siamés', 'Sphynx', 'Otra raza'],
        ],
        'exotico' => [
            'nombre' => 'Exótico',
            'icono' => 'mascota',
            'razas' => ['Conejo', 'Hámster', 'Cuy', 'Chinchilla', 'Hurón', 'Erizo', 'Ave', 'Tortuga', 'Reptil', 'Otro'],
        ],
    ],

    // factor: cuánto varía el valor de la atención en cada lugar
    'lugares' => [
        ['id' => 1, 'nombre' => 'Clínica Veterinaria Los Aromos', 'direccion' => 'Av. Los Leones 1240', 'factor' => 1],
        ['id' => 2, 'nombre' => 'Centro Veterinario San Francisco', 'direccion' => 'Av. Irarrázaval 3150', 'factor' => 1.1],
        ['id' => 3, 'nombre' => 'Hospital Veterinario del Parque', 'direccion' => 'Av. Pedro de Valdivia 845', 'factor' => 1.2],
        ['id' => 4, 'nombre' => 'Clínica VetSalud', 'direccion' => 'Av. Apoquindo 4521', 'factor' => 1.05],
    ],
];
