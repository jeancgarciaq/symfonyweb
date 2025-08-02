<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Post;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(unique: true, length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true, nullable: true)]
    private ?string $slug = null;


    /**
     * Many Tags have Many Posts
     * @var Collection<int, Post>
     */
    #[ORM\ManyToMany(targetEntity: Post::class, mappedBy: 'tags')]
    private Collection $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;
        return $this;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): static 
    {
        if (!$this->posts->contains($post)) {
            $this->posts->add($post);
        }
        return $this;
    }

    public function removePost(Post $post): static
    {
        $this->posts->removeElement($post);
        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? ''; // Útil para formularios de selección en EasyAdmin
    }

}
