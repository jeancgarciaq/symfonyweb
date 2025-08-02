<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
//use Symfony\Component\HttpFoundation\Request;


final class TagController extends AbstractController
{
    #[Route('/tag', name: 'app_tag_index')]
    public function index(TagRepository $tagRepository): Response
    {
        $tags = $tagRepository->findAll();

        return $this->render('tag/index.html.twig', [
            'tags' => $tags,
        ]);
    }

    #[Route('/tag/create', name: 'app_tag_create')]
    public function createTag(EntityManagerInterface $entityManager): Response
    {
        $tags = ['symfony', 'doctrine', 'php', 'backend', 'tutorial'];

        foreach ($tags as $tagName) {
            $tag = new Tag();
            $tag->setName($tagName);
            $tag->setSlug(str_replace(' ', '-', strtolower($tagName)));
            $entityManager->persist($tag);
        }

        $entityManager->flush();

        return new Response('Tags de prueba creados exitosamente.');
    }

    #[Route('/tag/{id}', name: 'app_tag_show')]
    public function show(Tag $tag): Response
    {
        return 'Método show';
    }

    #[Route('/tag/{id}/delete', name: 'app_tag_delete', methods: ['POST'])]
    public function delete(
        Request $request, // Necesitas inyectar Request para verificar el token CSRF
        Tag $tag, // Symfony encuentra el producto por el {id} de la URL
        EntityManagerInterface $entityManager
    ): Response {
        return 'Método delete';
    }

    #[Route('/tag/edit/{id}', name: 'app_tag_edit')]
    public function edit(Tag $tag, EntityManagerInterface $entityManager): Response
    {
        return 'Método edit';
    }

}
