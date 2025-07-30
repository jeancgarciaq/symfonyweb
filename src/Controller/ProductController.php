<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/product/create', name: 'app_product_create')]
    public function create(EntityManagerInterface $entityManager): Response
    {
        $product = new Product();
        $product->setName('Laptop XYZ');
        $product->setDescription('Una laptop potente para trabajo y juegos.');
        $product->setPrice(1200.00);

        // Persiste el objeto en la base de datos
        $entityManager->persist($product);
        $entityManager->flush(); // Guarda los cambios

        return new Response('Producto creado con ID: ' . $product->getId());
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(Product $product): Response
    {
        // Symfony automáticamente inyecta la entidad Product basada en el {id} de la URL
        // Si el producto no se encuentra, lanzará un 404
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/edit/{id}', name: 'app_product_edit')]
    public function edit(Product $product, EntityManagerInterface $entityManager): Response
    {
        // Supongamos que queremos cambiar el precio
        $product->setPrice(2550.00);
        $entityManager->flush(); // Guarda los cambios

        return new Response('Producto con ID: ' . $product->getId() . ' actualizado.');
    }

    #[Route('/products/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(
        Request $request, // Necesitas inyectar Request para verificar el token CSRF
        Product $product, // Symfony encuentra el producto por el {id} de la URL
        EntityManagerInterface $entityManager
    ): Response {
        // Validación del token CSRF: ¡Esto es CRUCIAL para la seguridad!
        // El nombre del token debe coincidir con el que generas en el formulario Twig ('delete' ~ product.id)
        if ($this->isCsrfTokenValid('delete' . $product->getId(), $request->request->get('_token'))) {
            $entityManager->remove($product); // Marca el objeto para eliminación
            $entityManager->flush(); // Ejecuta la eliminación en la base de datos

            // Añade un mensaje flash de éxito. 'success' es el tipo de mensaje.
            $this->addFlash('success', '✅ El producto "' . $product->getName() . '" ha sido eliminado correctamente.');

        } else {
            // Si el token CSRF no es válido, no se procesa la eliminación.
            $this->addFlash('error', '❌ Error de seguridad: el token de eliminación no es válido.');
        }

        // Redirige al usuario al listado de productos después de la operación.
        // Asume que la ruta al listado se llama 'app_product_index'.
        return $this->redirectToRoute('app_product_index');
    }
}