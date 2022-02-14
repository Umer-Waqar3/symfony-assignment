<?php

namespace App\Entity;

use App\Repository\FollowerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FollowerRepository::class)]
class Follower
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'followers')]
    private $followers;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'following')]
    private $following;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFollowers(): ?User
    {
        return $this->followers;
    }

    public function setFollowers(?User $followers): self
    {
        $this->followers = $followers;

        return $this;
    }

    public function getFollowing(): ?User
    {
        return $this->following;
    }

    public function setFollowing(?User $following): self
    {
        $this->following = $following;

        return $this;
    }
}
