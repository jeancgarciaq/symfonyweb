<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;


final class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category_index')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/category/create', name: 'app_category_create')]
    public function create(EntityManagerInterface $entityManager): Response
    {
        $category = new Category();
        $category->setName('Tecnología');
        $category->setSlug('tecnologia');
        // Persiste el objeto en la base de datos
        $entityManager->persist($category);
        $entityManager->flush(); // Guarda los cambios

        $category2 = new Category();
        $category2->setName('Noticias');
        $category2->setSlug('noticias');
        $entityManager->persist($category2);
        $entityManager->flush();

        return new Response('Categoria creada con ID: ' . $category->getId());
    }

    #[Route('/category/{id}', name: 'app_category_show')]
    public function show(category $category): Response
    {
        // Symfony automáticamente inyecta la entidad category basada en el {id} de la URL
        // Si el categoryo no se encuentra, lanzará un 404
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/category/edit/{id}', name: 'app_category_edit')]
    public function edit(category $category, EntityManagerInterface $entityManager): Response
    {
        // Supongamos que queremos cambiar el precio
        $category->setName('Laptop-PC');
        $entityManager->flush(); // Guarda los cambios

        return new Response('Categoria con ID: ' . $category->getId() . ' actualizada.');
    }

    #[Route('/category/{id}/delete', name: 'app_category_delete', methods: ['POST'])]
    public function delete(
        Request $request, // Necesitas inyectar Request para verificar el token CSRF
        category $category, // Symfony encuentra el categoryo por el {id} de la URL
        EntityManagerInterface $entityManager
    ): Response {
        // Validación del token CSRF: ¡Esto es CRUCIAL para la seguridad!
        // El nombre del token debe coincidir con el que generas en el formulario Twig ('delete' ~ category.id)
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category); // Marca el objeto para eliminación
            $entityManager->flush(); // Ejecuta la eliminación en la base de datos

            // Añade un mensaje flash de éxito. 'success' es el tipo de mensaje.
            $this->addFlash('success', '✅ La categoria "' . $category->getName() . '" ha sido eliminada correctamente.');

        } else {
            // Si el token CSRF no es válido, no se procesa la eliminación.
            $this->addFlash('error', '❌ Error de seguridad: el token de eliminación no es válido.');
        }

        // Redirige al usuario al listado de productos después de la operación.
        // Asume que la ruta al listado se llama 'app_product_index'.
        return $this->redirectToRoute('app_category_index');
    }
}
