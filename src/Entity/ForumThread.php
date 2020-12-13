<?php

namespace App\Entity;

use App\Repository\ForumThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ForumThreadRepository::class)
 */
class ForumThread
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $solved;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="forumThreads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity=Forum::class, inversedBy="forumThreads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $forum;

    /**
     * @ORM\OneToMany(targetEntity=ForumPost::class, mappedBy="thread", orphanRemoval=true)
     */
    private $forumPosts;

    /**
     * @ORM\OneToOne(targetEntity=ForumPost::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $firstPost;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastPostCreatedAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastPostAuthor;

    public function __construct()
    {
        $this->forumPosts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getSolved(): ?bool
    {
        return $this->solved;
    }

    public function setSolved(?bool $solved): self
    {
        $this->solved = $solved;

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

    public function getForum(): ?Forum
    {
        return $this->forum;
    }

    public function setForum(?Forum $forum): self
    {
        $this->forum = $forum;

        return $this;
    }

    /**
     * @return Collection|ForumPost[]
     */
    public function getForumPosts(): Collection
    {
        return $this->forumPosts;
    }

    public function addForumPost(ForumPost $forumPost): self
    {
        if (!$this->forumPosts->contains($forumPost)) {
            $this->forumPosts[] = $forumPost;
            $forumPost->setThread($this);
        }

        return $this;
    }

    public function removeForumPost(ForumPost $forumPost): self
    {
        if ($this->forumPosts->removeElement($forumPost)) {
            // set the owning side to null (unless already changed)
            if ($forumPost->getThread() === $this) {
                $forumPost->setThread(null);
            }
        }

        return $this;
    }

    public function getFirstPost(): ?ForumPost
    {
        return $this->firstPost;
    }

    public function setFirstPost(ForumPost $firstPost): self
    {
        $this->firstPost = $firstPost;

        return $this;
    }

    public function getLastPostCreatedAt(): ?\DateTimeInterface
    {
        return $this->lastPostCreatedAt;
    }

    public function setLastPostCreatedAt(\DateTimeInterface $lastPostCreatedAt): self
    {
        $this->lastPostCreatedAt = $lastPostCreatedAt;

        return $this;
    }

    public function getLastPostAuthor(): ?string
    {
        return $this->lastPostAuthor;
    }

    public function setLastPostAuthor(string $lastPostAuthor): self
    {
        $this->lastPostAuthor = $lastPostAuthor;

        return $this;
    }
}
