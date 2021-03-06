<?php
/**
 * This file is part of the PrestaCMSCoreBundle.
 *
 * (c) PrestaConcept <www.prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Presta\CMSCoreBundle\Controller;

use Presta\CMSCoreBundle\Model\Page;
use Presta\CMSCoreBundle\Model\PageManager;
use Presta\CMSCoreBundle\Model\ThemeManager;
use Presta\CMSCoreBundle\Model\WebsiteManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Nicolas Bastien <nbastien@prestaconcept.net>
 * @author Alain Flaus <aflaus@prestaconcept.net>
 */
class PageController extends Controller
{
    /**
     * @return WebsiteManager
     */
    protected function getWebsiteManager()
    {
        return $this->get('presta_cms.manager.website');
    }

    /**
     * @return ThemeManager
     */
    protected function getThemeManager()
    {
        return $this->get('presta_cms.manager.theme');
    }

    /**
     * @return PageManager
     */
    protected function getPageManager()
    {
        return $this->get('presta_cms.manager.page');
    }

    /**
     * Render a CMS page
     * Action that is mapped in the controller_by_class map
     *
     * @param  Page                  $contentDocument
     * @throws NotFoundHttpException
     */
    public function renderAction(Page $contentDocument)
    {
        $website = $this->getWebsiteManager()->getCurrentWebsite();
        if (!$contentDocument || ($contentDocument->getLocale() != $website->getLocale())) {
            throw new NotFoundHttpException('Content not found');
        }
        $request     = $this->getRequest();
        $pageManager = $this->getPageManager();

        //Cache validation
        $response = new Response();
        $response->setPublic();
        $response->setLastModified($contentDocument->getLastCacheModifiedDate());
        $previewToken   = $request->get('token', null);
        $isPreviewMode  = ($previewToken != null && $pageManager->isValidToken($contentDocument, $previewToken));
        $isCacheEnabled = ($isPreviewMode == false && $this->container->getParameter('presta_cms_core.cache.enabled'));

        if ($response->isNotModified($request) && $isCacheEnabled) {
            return $response;
        }

        $theme = $this->getThemeManager()->getTheme($website->getTheme(), $website);

        $this->get('sonata.seo.page')
            ->setTitle($contentDocument->getTitle())
            ->addMeta('name', 'keywords', $contentDocument->getMetaKeywords())
            ->addMeta('name', 'description', $contentDocument->getMetaDescription());

        $viewParams = array(
            'base_template'     => $theme->getTemplate(),
            'website'           => $website,
            'websiteManager'    => $this->getWebsiteManager(),
            'theme'             => $theme,
            'page'              => $contentDocument
        );

        $pageManager->setCurrentPage($contentDocument);
        $pageType = $pageManager->getPageType($contentDocument->getType());
        if ($pageType != null) {
            $viewParams = array_merge($viewParams, $pageType->getData($contentDocument));
        }
        $template = $viewParams['template'];

        if (!$isCacheEnabled) {
            //If cache is disabled we need to remove the cache data form the response
            $response = null;
        }

        return $this->render($template, $viewParams, $response);
    }
}
