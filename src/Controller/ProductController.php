<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        $product->setPrice(1150.00);
        $entityManager->flush(); // Guarda los cambios

        return new Response('Producto con ID: ' . $product->getId() . ' actualizado.');
    }

    #[Route('/product/delete/{id}', name: 'app_product_delete')]
    public function delete(Product $product, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($product); // Marca el objeto para eliminación
        $entityManager->flush(); // Ejecuta la eliminación

        return new Response('Producto con ID: ' . $product->getId() . ' eliminado.');
    }
}