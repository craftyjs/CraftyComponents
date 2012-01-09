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
            $url = $request->request->get('url');

            $urlArray = explode('/', $url);
            $repoOwner = $urlArray[count($urlArray) - 2];
            $repoName = str_replace('.git', '', $urlArray[count($urlArray) - 1]);

            $ch = curl_init();
            $repoUrl = $url = 'https://api.github.com/repos/'.$repoOwner.'/'.$repoName.'/git/trees/master';
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = ArrayService::objectToArray(json_decode(curl_exec($ch)));

            foreach($data['tree'] as $key => $value){
                $element = $value;

                if($element['path'] == 'package.json') {
                    $ch = curl_init();
                    $url = 'https://api.github.com/repos/'.$repoOwner.'/'.$repoName.'/git/blobs/'.$element['sha'];
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $package = ArrayService::objectToArray(json_decode(curl_exec($ch)));
                    $decodedPackage = utf8_encode(base64_decode($package['content']));
                    $parsedPackage = json_decode($decodedPackage, true);
                }
            };

            $files = array();
            $componentFilesValue = array();
            foreach($parsedPackage['files'] as $value) {
                $files[] = $value;
            }

            foreach($data['tree'] as $key => $value){
                $element = $value;

                if(in_array($element['path'], $files)) {
                    $ch = curl_init();
                    $url = 'https://api.github.com/repos/'.$repoOwner.'/'.$repoName.'/git/blobs/'.$element['sha'];
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $packageFile = ArrayService::objectToArray(json_decode(curl_exec($ch)));
                    $componentFilesValue[] = $packageFile['content'];
                }
            }

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
                'jsfiddle' => $parsedPackage['jsfiddle'],
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
                        if ((float)$value->getValue() > $tempMaxVersion){
                            $tempMaxVersion = (float)$value->getValue();
                            $latestVersion = $value;    
                        }
                    }
                }

                if( $latestVersion->getValue() != $componentData['version']['value']) {
                    $component = $this->_createComponent($component, $componentData);
                    $versionRelease = $this->_createVersion($componentData, $component, 'RELEASE');
                    $version = $this->_createVersion($componentData, $component);
                    $em->persist($component);
                    $em->persist($versionRelease);
                    $em->persist($version);
                } if ($latestVersion->getSha() != $componentData['version']['sha']) {
                    $version = $this->_createVersion($componentData, $component, 'DEV');
                    $em->persist($version);
                }
                
                $em->flush();
            }

            $component = $em->getRepository('FWMCraftyComponentsBundle:Components')->getOneWithVersions($component->getId())->getArrayResult();
            $component  = $component[0];

            foreach($component['versions'] as $value){
                if($value['value'] == 'RELEASE') {
                    $newVersion = $value;
                }
            }

            return new RedirectResponse($this->generateUrl('fwm_crafty_components_single', array(
                    'id' => $component['id']
            )));
        }

        return array('component' => false);
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

        file_put_contents(
            $this->get('request')->server->get('DOCUMENT_ROOT').'/uploads/components/'.$componentData['name'].'-'.$componentData['version']['value'].'-uncompresed.js', 
            base64_decode(implode(' ', json_decode($componentData['componentFilesValue'])))
        );

        $js = new AssetCollection(array(
            new FileAsset($this->get('request')->server->get('DOCUMENT_ROOT').'/uploads/components/'.$componentData['name'].'-'.$componentData['version']['value'].'-uncompresed.js'),
        ), array(
            new Yui\JsCompressorFilter($this->get('request')->server->get('DOCUMENT_ROOT').'/../app/Resources/java/yuicompressor.jar'),
        ));

        file_put_contents(
            $this->get('request')->server->get('DOCUMENT_ROOT').'/uploads/components/'.$componentData['name'].'-'.$componentData['version']['value'].'.js', $js->dump()
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
        $components = $em->getRepository('FWMCraftyComponentsBundle:Components')->getNew()->getResult();

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
            'components' => $componentsArray
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
