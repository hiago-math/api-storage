<?php

namespace Shared\DTO\Mangas;

use Shared\DTO\DTOAbstract;

class CreateMangaDTO extends DTOAbstract
{
    /** @var string  */
    public string $uid;

    /** @var string  */
    public string $nome;

    /** @var string  */
    public string $label;

    /** @var string  */
    public string $link;

    /** @var int  */
    public int $num_caps = 0;

    /**
     * @param string $uid
     * @param string $nome
     * @param string $label
     * @param string $link
     * @return CreateMangaDTO
     */
    public function register(string $uid, string $nome, string $label, string $link)
    {
        $this->uid = $uid;
        $this->nome = $nome;
        $this->label = $label;
        $this->link = $link;

        return $this;
    }
}
