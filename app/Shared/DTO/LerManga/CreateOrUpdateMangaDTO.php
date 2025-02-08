<?php

namespace Shared\DTO\LerManga;

use Shared\DTO\DTOAbstract;

class CreateOrUpdateMangaDTO extends DTOAbstract
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
    public int $total_chapters;

    /** @var bool  */
    public bool $sync = false;

    /** @var array  */
    public array $infos;

    /** @var array  */
    public array $chapters;

    /**
     * @param string $uid
     * @param string $nome
     * @param string $label
     * @param string $link
     * @param array $infos
     * @param int $total_chapters
     * @return CreateOrUpdateMangaDTO
     */
    public function register(string $uid, string $nome, string $label, string $link, array $infos, int $total_chapters): static
    {
        $this->uid = $uid;
        $this->nome = $nome;
        $this->label = $label;
        $this->link = $link;
        $this->infos = $infos;
        $this->total_chapters = $total_chapters;

        return $this;
    }
}
