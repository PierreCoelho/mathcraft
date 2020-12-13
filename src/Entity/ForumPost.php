<?php

namespace App\Entity;

use App\Repository\ForumPostRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ForumPostRepository::class)
 */
class ForumPost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $editedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $moderateReason;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $ip;

    /**
     * @ORM\Column(type="boolean")
     */
    private $helpedSolve;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="forumPosts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=ForumThread::class, inversedBy="forumPosts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $thread;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getEditedAt(): ?\DateTimeInterface
    {
        return $this->editedAt;
    }

    public function setEditedAt(?\DateTimeInterface $editedAt): self
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    public function getModerateReason(): ?string
    {
        return $this->moderateReason;
    }

    public function setModerateReason(?string $moderateReason): self
    {
        $this->moderateReason = $moderateReason;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getHelpedSolve(): ?bool
    {
        return $this->helpedSolve;
    }

    public function setHelpedSolve(bool $helpedSolve): self
    {
        $this->helpedSolve = $helpedSolve;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getThread(): ?ForumThread
    {
        return $this->thread;
    }

    public function setThread(?ForumThread $thread): self
    {
        $this->thread = $thread;

        return $this;
    }
}
