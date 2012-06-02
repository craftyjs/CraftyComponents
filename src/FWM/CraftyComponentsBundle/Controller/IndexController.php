<?php

namespace FWM\CraftyComponentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use FWM\ServicesBundle\Services\ArrayService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    /**
     * @Route("/components/addNew", name="fwm_crafty_components_add_new")
     * @Template()
     */
    public function addNewAction(Request $request)
    {
        if($request->getMethod() == 'POST') {
            // serve Post-Receive URL
            if($request->request->has('payload')) {
                $posteRecive = ArrayService::objectToArray(json_decode($request->request->get('payload')));
                $request->request->set('url', $posteRecive['repository']['url']);
            }

            $repoData = array();
            if($request->request->has('url')) {
                $url = $request->request->get('url');
                $urlArray = explode('/', $url);
                $repoData['repoOwner'] = $urlArray[count($urlArray) - 2];
                $repoData['repoName'] = str_replace('.git', '', $urlArray[count($urlArray) - 1]);
                $repoUrl = 'https://api.github.com/repos/'.$repoData['repoOwner'].'/'.$repoData['repoName'].'/git/trees/master';
            } else if($request->request->has('repo_url')) {
                $url = $request->request->get('repo_url');
                $urlArray = explode('/', $url);
                $repoData['repoOwner'] = $urlArray[count($urlArray) - 5];
                $repoData['repoName'] = $urlArray[count($urlArray) - 4];
                $repoUrl = $request->request->get('repo_url');
            } else {
                return array('component' => false);
            }

            $component = $this->_serveUpdateRequest($request, $repoUrl, $repoData);
            
            $em = $this->getDoctrine()->getEntityManager();
            $component = $em->getRepository('FWMCraftyComponentsBundle:Components')
                ->getOneWithVersions($component->getId())
                ->getArrayResult();

            return new RedirectResponse($this->generateUrl('fwm_crafty_components_single', array(
                'id' => $component[0]['id']
            )));
        }

        return array('component' => false);
    }

    private function _createRepoUrl($repoData, $branch = false){
        $repoUrl = 'https://api.github.com/repos/'.$repoData['repoOwner'].'/'.$repoData['repoName'].'/git/trees/master';
        if ($branch != false) {
            $repoUrl = 'https://api.github.com/repos/'.$repoData['repoOwner'].'/'.$repoData['repoName'].'/git/trees/'.$branch;
        }

        return $repoUrl;
    }

    private function _serveUpdateRequest($request, $repoUrl, $repoData, $branch = false) {
        $repoOwner  = $repoData['repoOwner'];
        $repoName   = $repoData['repoName'];

        $ch         = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_createRepoUrl($repoData, $branch));
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data       = ArrayService::objectToArray(json_decode(curl_exec($ch)));

        if (!array_key_exists('tree', $data)) {
            return array(
                'component' => false,
                'errors' => array(
                    0 => 'This repository address ('.$url.') is invalid.'
                )
            );
        }

        // fetch repo files
        foreach($data['tree'] as $key => $value){
            $element = $value;
            if($element['path'] == 'package.json') {
                $ch     = curl_init();
                $url    = 'https://api.github.com/repos/'.$repoOwner.'/'.$repoName.'/git/blobs/'.$element['sha'];
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $package        = ArrayService::objectToArray(json_decode(curl_exec($ch)));
                $decodedPackage = base64_decode($package['content']);
                if(mb_detect_encoding(base64_decode($package['content']), "UTF-8") != 'UTF-8') {
                    $decodedPackage = utf8_encode($decodedPackage);
                } else {
                    $decodedPackage = preg_replace('/[^(\x20-\x7F)]*/','', $decodedPackage);    
                }
                $parsedPackage = ArrayService::objectToArray(json_decode($decodedPackage));
            }
        };

        $files                  = array();
        $dirs                   = array();
        $componentFilesValue    = array();
        $dirs                   = $this->_findDirsAndFiles($parsedPackage['files'], array('/' => array()), '/');

        // Load files form package.js
        $componentFilesValue    =  $this->_getFilesFromDirs($componentFilesValue, $data['tree'], $dirs['/'], $namespace = '/');

        $componentData = array(
            'repoUrl'               => $this->_createRepoUrl($repoData, false),
            'name'                  => $parsedPackage['name'],
            'version'               => array(
                'value'                 => $parsedPackage['version'],
                'sha'                   => $package['sha']
            ),
            'title'                 => $parsedPackage['title'],
            'author'                => array(
                'name'                  => $parsedPackage['author']['name'],
                'url'                   => $parsedPackage['author']['url'],
            ),
            'license'               => array(
                'type'                  => $parsedPackage['licenses'][0]['type'],
                'url'                   => $parsedPackage['licenses'][0]['url'],
            ),
            'tags'                  => $parsedPackage['keywords'],
            'description'           => $parsedPackage['description'],
            'homepage'              => $parsedPackage['homepage'],
            'jsfiddle'              => array_key_exists('jsfiddle', $parsedPackage)? $parsedPackage['jsfiddle'] : null,
            'componentFilesValue'   => json_encode($componentFilesValue)
        );

        $em                     = $this->getDoctrine()->getEntityManager();
        $componentRepository    = $em->getRepository('FWMCraftyComponentsBundle:Components');
        $versionsRepository     = $em->getRepository('FWMCraftyComponentsBundle:Versions');
        $tagsRepository         = $em->getRepository('FWMCraftyComponentsBundle:Tags');

        $component              = $componentRepository->findOneBy(array('repoUrl' => $componentData['repoUrl']));

        if (!$component) {

            /**
             * First creation component and versions
             */
            $component      = new \FWM\CraftyComponentsBundle\Entity\Components();
            $component      = $componentRepository->fillComponent($component, $componentData);
            $version        = $versionsRepository->createVersion($request, $componentData, $component, false, $branch);
            $versionRelease = $versionsRepository->createVersion($request, $componentData, $component, 'RELEASE', $branch);
            $versionDev     = $versionsRepository->createVersion($request, $componentData, $component, 'DEV', $branch);

            $em->persist($component);
            $em->persist($version);
            $em->persist($versionRelease);
            $em->persist($versionDev);
            $em->flush();

        } else {
            /**
             * Update component data form package.json and create new versions when needed
             *
             * Find max component version
             */
            $latestVersion      = false;
            $latestDevVersion   = false;
            $tempMaxVersion     = 0;

            foreach ($component->getVersions() as $value){
                $release = 'RELEASE';            
                if ($branch != null) {
                    $release = $release.'-'.$branch; 
                }

                $dev = 'DEV';
                if ($branch != null) {
                    $dev = $dev.'-'.$branch; 
                }

                $valueVersion = $value->getValue();
                if ($branch) {
                    $valueVersion = str_replace('-'.$branch, '', $valueVersion);
                }

                if ($valueVersion != $release && $valueVersion != $dev) {
                    if (version_compare($valueVersion, $tempMaxVersion, '>')){
                        $tempMaxVersion = $valueVersion;
                        $latestVersion = $value;
                        print_r($valueVersion.' > '.$tempMaxVersion);
                    }
                }

                if ($value->getValue() == $dev) {
                    $oldDevValue = $value->getFileContent();
                    $latestDevVersion = $value;
                };
            }

            /**
             * If current version is different form latest in database create  release version, 
             * standard version and update component data
             */
            if ($latestVersion) {
                if ( $latestVersion->getValue() != $componentData['version']['value'] ) {
                    $component = $componentRepository->fillComponent($component, $componentData);
                    $versionRelease = $versionsRepository->createVersion($request, $componentData, $component, 'RELEASE', $branch);
                    $version = $versionsRepository->createVersion($request, $componentData, $component, false, $branch);
                    $em->persist($versionRelease);
                    $em->persist($version);
                }
            }

            /**
             * If current version is this same as latest in database but files values is different create dev version 
             * and update component data
             */
            if ($latestDevVersion) {
                if (
                    sha1($componentData['componentFilesValue']) != sha1($oldDevValue) || 
                    $componentData['version']['sha'] != $latestDevVersion->getSha()
                ){
                    $component  = $componentRepository->fillComponent($component, $componentData);
                    $versionDev = $versionsRepository->createVersion($request, $componentData, $component, 'DEV', $branch);
                    $em->persist($versionDev);
                }
            }

            /**
             * Create first versions for additional branch.
             */
            if(!$latestVersion && !$latestDevVersion) {
                $component      = $componentRepository->fillComponent($component, $componentData);
                $versionRelease = $versionsRepository->createVersion($request, $componentData, $component, 'RELEASE', $branch);
                $versionDev     = $versionsRepository->createVersion($request, $componentData, $component, 'DEV', $branch);
                $version        = $versionsRepository->createVersion($request, $componentData, $component, false, $branch);
                $em->persist($versionRelease);
                $em->persist($versionDev);
                $em->persist($version);
            }

            $tagsRepository->synchronizeTags($componentData['tags'], $component);

            $em->flush();
        }

        /**
         * Parese extra defined branches
         */
        if (array_key_exists('branches', $parsedPackage) && $branch == false) {
            foreach($parsedPackage['branches'] as $branchName => $value) {
                $repoUrl = $this->_createRepoUrl($repoData, $branchName);
                $this->_serveUpdateRequest($request, $repoUrl, $repoData, $branchName);
            }
        }

        return $component;
    }

    private function _findDirsAndFiles (array $files, $dirs, $namespace) {
        foreach( $files as $value) {
            $arrayValue = explode('/', $value);
            if(count($arrayValue) > 1) {
                $dirs[$namespace][$arrayValue[0]][] = $arrayValue[1];
                $arrayValue = explode('/', $arrayValue[1]);
                if(count($arrayValue) > 1) {
                    $this->_findDirsAndFiles ($files, $dirs, $arrayValue[1]);
                }
            } else {
                $dirs[$namespace][] = $value;
            }
        }

        return $dirs;
    }

    private function _loadFileContentFromGithub($componentFilesValue, $url, $componentFilesValueKey) {
        $ch = curl_init();
        $url = $url;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $packageFile = ArrayService::objectToArray(json_decode(curl_exec($ch)));
        $componentFilesValue[$componentFilesValueKey] = $packageFile['content'];

        return $componentFilesValue;
    }

    private function _getFilesFromDirs($componentFilesValue, $data, $dirs, $namespace = '/') {
        foreach( $data as $key => $element){
            if($element['type'] == 'tree' && array_key_exists($element['path'], $dirs)) {
                $ch = curl_init();
                $url = $element['url'];
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $packageFile = ArrayService::objectToArray(json_decode(curl_exec($ch)));
                $dirData = $packageFile['tree'];
                $componentFilesValue = $this->_getFilesFromDirs($componentFilesValue, $dirData, $dirs[$element['path']], $element['path']);
            } else if($element['type'] == 'blob' && in_array($element['path'], $dirs)) {$componentFilesValue = $this->_loadFileContentFromGithub($componentFilesValue, $element['url'], array_search($element['path'], $dirs));
            }
        };
        ksort($componentFilesValue);
        return $componentFilesValue;
    }


    /**
     * @Route("/", name="fwm_crafty_components_main")
     * @Route("/components/list", name="fwm_crafty_components_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $craftyComponentsConfig = $this->container->getParameter('fwm_crafty_components');
        $components = $em->getRepository('FWMCraftyComponentsBundle:Components')->getNew($craftyComponentsConfig);

        $crafty = $em->getRepository('FWMCraftyComponentsBundle:Components')
            ->getOneWithVersions($craftyComponentsConfig['crafty']['id'])
            ->getSingleResult();
        $craftyVersions = $crafty->getVersions();

        $latestCrafty = $craftyVersions[0];
        foreach ($craftyVersions as $version) {
            if ($version->getValue() != 'DEV' && $version->getValue() != 'RELEASE') {
                if (version_compare($version->getValue(), $latestCrafty->getValue(), '>')){
                    $latestCrafty = $version;
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $components = $paginator->paginate(
            $components,
            $this->get('request')->query->get('page', 1),
            10
        );

        $componentsArray = array();
        foreach($components as $comp) {
            foreach($comp->getVersions() as $value){
                if($value->getValue() == 'RELEASE') {
                    $newVersion = $value;    
                }
            }

            $componentsArray[] = array('component' => $comp, 'version' => $newVersion);
        }

        return array(
            'components' => $componentsArray, 
            'componentsPaginator' => $components,
            'crafty' => $crafty,
            'craftyVersion' => $latestCrafty
        );
    }

    /**
     * @Route("/components/single/{id}/{name}", name="fwm_crafty_components_single", defaults={"name" = false})
     * @Template()
     */
    public function singleAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $component = $em->getRepository('FWMCraftyComponentsBundle:Components')->getOneWithVersions($id)->getArrayResult();
        $component  = $component[0];

        return array(
            'component' => $component
        );
    }

}


