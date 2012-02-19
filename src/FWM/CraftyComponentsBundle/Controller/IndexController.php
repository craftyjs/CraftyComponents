<?php

namespace FWM\CraftyComponentsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use FWM\ServicesBundle\Services\ArrayService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Assetic\Asset\GlobAsset;
use Assetic\Filter\LessFilter;
use Assetic\Filter\Yui;

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

            if($request->request->has('url')) {
                $url = $request->request->get('url');
                $urlArray = explode('/', $url);
                $repoOwner = $urlArray[count($urlArray) - 2];
                $repoName = str_replace('.git', '', $urlArray[count($urlArray) - 1]);
                $repoUrl = 'https://api.github.com/repos/'.$repoOwner.'/'.$repoName.'/git/trees/master';
            } else if($request->request->has('repo_url')) {
                $url = $request->request->get('repo_url');
                $urlArray = explode('/', $url);
                $repoOwner = $urlArray[count($urlArray) - 5];
                $repoName = $urlArray[count($urlArray) - 4];
                $repoUrl = $request->request->get('repo_url');
            } else {
                return array('component' => false);
            }

            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $repoUrl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = ArrayService::objectToArray(json_decode(curl_exec($ch)));

            // fetch repo files
            foreach($data['tree'] as $key => $value){
                $element = $value;
                if($element['path'] == 'package.json') {
                    $ch = curl_init();
                    $url = 'https://api.github.com/repos/'.$repoOwner.'/'.$repoName.'/git/blobs/'.$element['sha'];
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $package = ArrayService::objectToArray(json_decode(curl_exec($ch)));
                    $decodedPackage = base64_decode($package['content']);
                    if(mb_detect_encoding(base64_decode($package['content']), "UTF-8") != 'UTF-8') {
                        $decodedPackage = utf8_encode($decodedPackage);
                    } else {
                        $decodedPackage = preg_replace('/[^(\x20-\x7F)]*/','', $decodedPackage);    
                    }
                    $parsedPackage = ArrayService::objectToArray(json_decode($decodedPackage));
                }
            };

            $files = array();
            $dirs = array();
            $componentFilesValue = array();
            
            $dirs = $this->_findDirsAndFiles($parsedPackage['files'], array('/' => array()), '/');
            // Load files form package.js
            $componentFilesValue =  $this->_getFilesFromDirs($componentFilesValue, $data['tree'], $dirs['/'], $namespace = '/');

            $componentData = array(
                'repoUrl' => $repoUrl,
                'name' => $parsedPackage['name'],
                'version' => array(
                    'value' => $parsedPackage['version'],
                    'sha' => $package['sha']
                ),
                'title' => $parsedPackage['title'],
                'author' => array(
                    'name' => $parsedPackage['author']['name'],
                    'url' => $parsedPackage['author']['url'],
                ),
                'license' => array(
                    'type' => $parsedPackage['licenses'][0]['type'],
                    'url' => $parsedPackage['licenses'][0]['url'],
                ),
                'description' => $parsedPackage['description'],
                'homepage' => $parsedPackage['homepage'],
                'jsfiddle' => array_key_exists('jsfiddle', $parsedPackage)? $parsedPackage['jsfiddle'] : null,
                'componentFilesValue' => json_encode($componentFilesValue)
            );

            $em = $this->getDoctrine()->getEntityManager();

            $component = $em->getRepository('FWMCraftyComponentsBundle:Components')->findOneBy(array(
                'repoUrl' => $componentData['repoUrl']
            ));

            if(!$component) {
                $component = new \FWM\CraftyComponentsBundle\Entity\Components();
                $component = $this->_createComponent($component, $componentData);
                $version = $this->_createVersion($componentData, $component);
                $versionRelease = $this->_createVersion($componentData, $component, 'RELEASE');
                $versionDev = $this->_createVersion($componentData, $component, 'DEV');

                $em->persist($component);
                $em->persist($version);
                $em->persist($versionRelease);
                $em->persist($versionDev);
                $em->flush();
            } else {
                $tempMaxVersion = 0;
                foreach ($component->getVersions() as $value){
                    if($value->getValue() != 'RELEASE' && $value->getValue() != 'DEV') {
                        if (version_compare($value->getValue(), $tempMaxVersion, '>')){
                            $tempMaxVersion = (float)$value->getValue();
                            $latestVersion = $value;    
                        }
                    }

                    if($value->getValue() == 'DEV') {
                        $oldDevValue = $value->getFileContent();
                        $latestDevVersion = $value;
                    };
                };

                if( $latestVersion->getValue() != $componentData['version']['value']) {
                    $component = $this->_createComponent($component, $componentData);
                    $versionRelease = $this->_createVersion($componentData, $component, 'RELEASE');
                    $version = $this->_createVersion($componentData, $component);
                    $em->persist($component);
                    $em->persist($versionRelease);
                    $em->persist($version);
                } if (
                    sha1($componentData['componentFilesValue']) != sha1($oldDevValue) || 
                    $componentData['version']['sha'] != $latestDevVersion->getSha()) 
                {
                    $component = $this->_createComponent($component, $componentData);
                    $version = $this->_createVersion($componentData, $component, 'DEV');
                    $em->persist($version);
                    $em->persist($component);
                }

                $em->flush();
            }

            $component = $em->getRepository('FWMCraftyComponentsBundle:Components')->getOneWithVersions($component->getId())->getArrayResult();

            foreach($component[0]['versions'] as $value){
                if($value['value'] == 'RELEASE') {
                    $newVersion = $value;
                }
            }

            return new RedirectResponse($this->generateUrl('fwm_crafty_components_single', array(
                'id' => $component[0]['id']
            )));
        }

        return array('component' => false);
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
            } else if($element['type'] == 'blob' && in_array($element['path'], $dirs)) {
                $componentFilesValue = $this->_loadFileContentFromGithub($componentFilesValue, $element['url'], array_search($element['path'], $dirs));
            }
        };
        ksort($componentFilesValue);
        return $componentFilesValue;
    }

    private function _createComponent($component, $componentData) {
        $component->setName($componentData['name']);
        $component->setTitle($componentData['title']);
        $component->setAuthorName($componentData['author']['name']);
        $component->setAuthorUrl($componentData['author']['url']);
        $component->setLicenseType($componentData['license']['type']);
        $component->setLicenseUrl($componentData['license']['url']);
        $component->setDescription($componentData['description']);
        $component->setHomepage($componentData['homepage']);
        $component->setRepoUrl($componentData['repoUrl']);
        $component->setJsfiddle($componentData['jsfiddle']);

        return $component;
    }

    private function _createVersion($componentData, $component, $versionType = false) {
        $em = $this->getDoctrine()->getEntityManager();

        if(!$versionType) {
            $version = new \FWM\CraftyComponentsBundle\Entity\Versions();
            $version->setValue($componentData['version']['value']);
        } else {
            $componentData['version']['value'] = $versionType;
            $version = $em->getRepository('FWMCraftyComponentsBundle:Versions')->findOneBy(array(
                'component' => $component->getId(),
                'value' => $versionType
            ));

            if(!$version) {
                $version = new \FWM\CraftyComponentsBundle\Entity\Versions();
                $version->setValue($versionType);
            }
        }

        $version->setSha($componentData['version']['sha']);
        $version->setComponent($component);
        $version->setFileContent($componentData['componentFilesValue']);
        $version->setCreatedAt(new \DateTime());

        foreach(ArrayService::objectToArray(json_decode($componentData['componentFilesValue'])) as $value) {
            $tempFileContent[] = base64_decode($value);
        };
        $file = implode(' ', $tempFileContent);

        file_put_contents(
            $this->get('request')->server->get('DOCUMENT_ROOT').'/uploads/components/'.strtolower($componentData['name']).'-'.strtolower($componentData['version']['value']).'-uncompressed.js', $file
        );

        try {
            $js = new AssetCollection(array(
                new FileAsset($this->get('request')->server->get('DOCUMENT_ROOT').'/uploads/components/'.strtolower($componentData['name']).'-'.strtolower($componentData['version']['value']).'-uncompressed.js'),
            ), array(
                new Yui\JsCompressorFilter($this->get('request')->server->get('DOCUMENT_ROOT').'/../app/Resources/java/yuicompressor.jar'),
            ));

            $minifidedFile = $js->dump();
        } catch(\Exception $e) {
            $minifidedFile = $file;
        };

        file_put_contents(
            $this->get('request')->server->get('DOCUMENT_ROOT').'/uploads/components/'.strtolower($componentData['name']).'-'.strtolower($componentData['version']['value']).'.js', $minifidedFile
        );

        return $version;
    }

    /**
     * @Route("/", name="fwm_crafty_components_main")
     * @Route("/components/list", name="fwm_crafty_components_list")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $components = $em->getRepository('FWMCraftyComponentsBundle:Components')->getNew();

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
            'components' => $componentsArray, 'componentsPaginator' => $components
        );
    }

    /**
     * @Route("/components/single/{id}", name="fwm_crafty_components_single")
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


