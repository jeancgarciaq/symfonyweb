<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Post;
use App\Entity\Category; // Importa la entidad Category
use App\Entity\Comment;
use App\Repository\PostRepository;
use App\Repository\CategoryRepository; // Importa el repositorio de Category
use Doctrine\ORM\EntityManagerInterface;

final class PostController extends AbstractController
{
    #[Route('/post', name: 'app_post_index')]
    public function index(PostRepository $postRepository): Response
    {
        $posts = $postRepository->findAll();

        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }

    #[Route('/post/create', name: 'app_post_create')]
    public function create(EntityManagerInterface $entityManager, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->findOneBy(['slug' => 'tecnologia']); // Busca una categoría existente

        if (!$category) {
            return new Response('La categoría "Tecnología" no existe. Por favor, créala primero.', 404);
        }

        $post = new Post();
        $post->setTitle('Symfony y las Relaciones de Entidades');
        $post->setContent('Este post explica cómo usar las relaciones de Doctrine en Symfony.');
        $post->setCategory($category); // ASIGNAMOS LA CATEGORÍA

        $entityManager->persist($post);
        $entityManager->flush();

        return new Response('Post creado con ID: ' . $post->getId() . ' en la categoría: ' . $post->getCategory()->getName());
    }

    #[Route('/post/{id}', name: 'app_post_show')]
    public function show(Post $post): Response
    {
        // El Post tiene acceso directo a su Category
        $categoryName = $post->getCategory() ? $post->getCategory()->getName() : 'Sin Categoría';

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'categoryName' => $categoryName,
        ]);
    }

    #[Route('/post/edit/{id}', name: 'app_post_edit')]
    public function edit(Post $post, EntityManagerInterface $entityManager): Response
    {
        // Supongamos que queremos cambiar el precio
        $post->setTitle('Prueba de edición');
        $post->setContent('Estoy probando que se puede editar el contenido de los post.');
        $entityManager->flush(); // Guarda los cambios

        return new Response('Post con ID: ' . $post->getId() . ' actualizado.');
    }

    #[Route('/post/{id}/delete', name: 'app_post_delete', methods: ['POST'])]
    public function delete(
        Request $request, // Necesitas inyectar Request para verificar el token CSRF
        Post $post, // Symfony encuentra el producto por el {id} de la URL
        EntityManagerInterface $entityManager
    ): Response {
        // Validación del token CSRF: ¡Esto es CRUCIAL para la seguridad!
        // El nombre del token debe coincidir con el que generas en el formulario Twig ('delete' ~ product.id)
        if ($this->isCsrfTokenValid('delete' . $post>getId(), $request->request->get('_token'))) {
            $entityManager->remove($post); // Marca el objeto para eliminación
            $entityManager->flush(); // Ejecuta la eliminación en la base de datos

            // Añade un mensaje flash de éxito. 'success' es el tipo de mensaje.
            $this->addFlash('success', '✅ El post "' . $post>getName() . '" ha sido eliminado correctamente.');

        } else {
            // Si el token CSRF no es válido, no se procesa la eliminación.
            $this->addFlash('error', '❌ Error de seguridad: el token de eliminación no es válido.');
        }

        // Redirige al usuario al listado de productos después de la operación.
        // Asume que la ruta al listado se llama 'app_product_index'.
        return $this->redirectToRoute('app_post_index');
    }

    #[Route('/post/{id}/add-comment', name: 'app_post_add_comment')]
    public function addComment(Post $post, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setAuthor('Visitante ' . random_int(1, 100));
        $comment->setContent('¡Excelente publicación sobre Symfony y Doctrine!');
        $comment->setPost($post); // ASIGNAMOS EL POST AL COMENTARIO

        // La relación bidireccional también puede ser gestionada por el lado 'One' (Post)
        // $post->addComment($comment); // Esto es opcional si ya usaste setPost($post)

        $entityManager->persist($comment);
        $entityManager->flush();

        return new Response('Comentario añadido a la publicación con ID: ' . $post->getId());
    }
}
