<?php
namespace Vadkuz\Flarum2Blog\Api\Controller;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\Api\Controller\AbstractDeleteController;
use Flarum\Http\RequestUtil;
use Illuminate\Contracts\Filesystem\Factory;
use Illuminate\Contracts\Filesystem\Filesystem;
use Psr\Http\Message\ServerRequestInterface;

class DeleteDefaultBlogImageController extends AbstractDeleteController
{
    protected Filesystem $uploadDir;

    /**
     * @param SettingsRepositoryInterface $settings
     */
    public function __construct(
        protected SettingsRepositoryInterface $settings,
        Factory $filesystemFactory
    ) {
        $this->uploadDir = $filesystemFactory->disk('flarum-assets');
    }
    /**
     * {@inheritdoc}
     */
    protected function delete(ServerRequestInterface $request): void
    {
        RequestUtil::getActor($request)->assertAdmin();

        $path = $this->settings->get('blog_default_image_path');
        $this->settings->set('blog_default_image_path', null);

        if ($this->uploadDir->exists($path)) {
            $this->uploadDir->delete($path);
        }
    }
}
