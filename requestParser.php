<?php
class RequestParser {
    private string $request;
    private ?string $headPath = null;
    private ?string $pagePath = null;

    public function __construct(string $requestURI)
    {
        $this->request = $requestURI;
    }

    public function parse() {
        $requestComponents = $this->getRequestParts();
        $page = array_pop($requestComponents);
        $dir = implode('/', $requestComponents) . (count($requestComponents) != 0 ? "/" : "");

        if (empty($page)) {
            $page = "index";
        }

        $pageFileName = pathinfo($page, PATHINFO_FILENAME);
        $pageFileType = pathinfo($page, PATHINFO_EXTENSION);
        $pageFileType = !empty($pageFileType) ? $pageFileType : $this->findFileType("content/pages/".$dir.$pageFileName, ["html", "php"]);
        $this->pagePath = "content/pages/".$dir.$pageFileName.".".$pageFileType;

        $headFiletype = $this->findFileType("content/heads/".$dir.$pageFileName, ["html", "php"]);
        $headFile = !$headFiletype ? "index.html" : $pageFileName.".".$headFiletype;
        $this->headPath = "content/heads/".$dir.$headFile;
    }

    /**
     * @return string
     */
    public function getRequest(): string
    {
        return $this->request;
    }

    public function getRequestPart(int $index) : ?string {
        $requestParts = $this->getRequestParts();

        if ($index >= count($requestParts)) {
            return null;
        }

        return $requestParts[$index];
    }

    public function getRequestParts() : array {
        return explode('/', $this->request);;
    }

    /**
     * @return string|null
     */
    public function getHeadPath(): ?string
    {
        return $this->headPath;
    }

    /**
     * @return string|null
     */
    public function getPagePath(): ?string
    {
        return $this->pagePath;
    }

    /**
     * @return ?string returns the first found filetype in the path. If nothing was found returns NULL.
     */
    private function findFileType(string $path, array $filetypes): ?string {
        for ($i = 0; $i < count($filetypes); $i++) {
            $filetype = $filetypes[$i];

            if (file_exists($path.".".$filetype)) {
                return $filetype;
            }
        }

        return null;
    }
}