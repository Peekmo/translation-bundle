<?php

namespace Umanit\TranslationBundle\Doctrine\Model;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait TranslatableTrait
{
    /**
     * @var UuidInterface
     * @ORM\Id()
     * @ORM\Column(name="uuid", type="string", length=36)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Id()
     * @ORM\Column(name="locale", type="string", length=7)
     */
    protected $locale;

    /**
     * @var array
     * @ORM\Column(type="json")
     */
    protected $translations = [];

    /**
     * TranslatableTrait constructor.
     *
     * @param string             $locale
     * @param UuidInterface|null $uuid
     */
    public function __construct(string $locale = null, UuidInterface $uuid = null)
    {
        if (null === $uuid) {
            $uuid = Uuid::uuid4();
        }

        $this->locale = $locale;
        $this->uuid   = (string) $uuid;
    }

    /**
     * Set the locale
     *
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale(string $locale = null): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Returns entity's locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set the UUID
     *
     * @param string $uuid
     *
     * @return $this
     */
    public function setUuid(string $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * Returns entity's UUID.
     *
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return array
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }

    /**
     * @param array $translations
     *
     * @return $this
     */
    public function setTranslations(array $translations): TranslatableInterface
    {
        $this->translations = $translations;

        return $this;
    }
}
