<?php
/**
 * This file is part of the Presta Bundle project.
 *
 * (c) PrestaConcept http://www.prestaconcept.net
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Presta\CMSCoreBundle\Controller;

use Presta\CMSCoreBundle\Model\Page;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Nicolas Bastien <nbastien@prestaconcept.net>
 * @author Alain Flaus <aflaus@prestaconcept.net>
 */
class PageController extends Controller
{
    /**
     * @return Presta\CMSCoreBundle\Model\WebsiteManager
     */
    protected function getWebsiteManager()
    {
        return $this->get('presta_cms.manager.website');
    }

    /**
     * @return Presta\CMSCoreBundle\Model\ThemeManager
     */
    protected function getThemeManager()
    {
        return $this->get('presta_cms.manager.theme');
    }

    /**
     * @return Presta\CMSCoreBundle\Model\PageManager
     */
    protected function getPageManager()
    {
        return $this->get('presta_cms.manager.page');
    }

    /**
     * Render a CMS page
     * Action that is mapped in the controller_by_class map
     *
     * @param  Page $contentDocument
     * @throws NotFoundHttpException
     */
    public function renderAction(Page $contentDocument)
    {
        if (!$contentDocument) {
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

        $website = $this->getWebsiteManager()->getCurrentWebsite();
        $theme   = $this->getThemeManager()->getTheme($website->getTheme(), $website);

        //If document load doesn't have the same locale as the website
        //Try to redirect on the translated page
        if ($contentDocument->getLocale() != $website->getLocale()) {
            throw new NotFoundHttpException('Content not found for this locale');
        }
        //Check if the document is publish and load the good version
        //todo when jackaplone implements it

        $seoPage = $this->get('sonata.seo.page');

        $seoPage
            ->setTitle($contentDocument->getTitle())
            ->addMeta('name', 'keywords', $contentDocument->getMetaKeywords())
            ->addMeta('name', 'description', $contentDocument->getMetaDescription());

        $viewParams = array(
            'base_template' => $theme->getTemplate(),
            'website' => $website,
            'websiteManager' => $this->getWebsiteManager(),
            'theme' => $theme,
            'page'  => $contentDocument
        );

        $pageManager->setCurrentPage($contentDocument);
        $pageType = $pageManager->getPageType($contentDocument->getType());
        if ($pageType != null) {
            $viewParams = array_merge($viewParams, $pageType->getData($contentDocument));
        }
        //todo voir pour une meileur initialisation
        //on doit charger directement le tempalte pour que ce denier puisse surcharge
        //des partie du layout librement
        $template = $viewParams['template'];

        if (!$isCacheEnabled) {
            //If cache is disabled we need to remove the cache data form the response
            $response = null;
        }

        return $this->render($template, $viewParams, $response);
    }
}
