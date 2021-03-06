<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class TagController extends BaseController
{
    /**
     * 获取所有标签，以JSONM的方式返回数据
     * 
     * @return JSONM Response
     */
    public function indexAction()
    {   
        $tags = $this->getTagService()->findAllTags(0, 100);

        return $this->render('TopxiaWebBundle:Tag:index.html.twig',array(
            'tags'=>$tags
        ));
    }

    public function showAction(Request $request,$name)
    {   
        $courses = $paginator = null;

        $tag = $this->getTagService()->getTagByName($name);

        if($tag) {  
            $conditions = array(
                'status' => 'published',
                'tagId' => $tag['id'],
                'parentId' => 0
            );

            $paginator = new Paginator(
                $this->get('request'),
                $this->getCourseService()->searchCourseCount($conditions)
                , 12
            );       

            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                'latest',
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }
        return $this->render('TopxiaWebBundle:Tag:show.html.twig',array(
            'tag'=>$tag,
            'courses'=>$courses,
            'paginator' => $paginator,
        ));
    }

    public function allAction()
    {
        $data = array();

        $tags = $this->getTagService()->findAllTags(0, 100);
        foreach ($tags as $tag) {
            $data[] = array('id' => $tag['id'],  'name' => $tag['name'] );
        }
        return $this->createJsonmResponse($data);
    }

    public function matchAction(Request $request)
    {
        $data = array();
        $queryString = $request->query->get('q');
        $callback = $request->query->get('callback');
        $tags = $this->getTagService()->getTagByLikeName($queryString);
        foreach ($tags as $tag) {
            $data[] = array('id' => $tag['id'],  'name' => $tag['name'] );
        }
        return new JsonResponse($data);
    }

    public function cityAction(Request $request)
    {
        $data = array();
        $province_id = $request->query->get('province_id');
        $callback = $request->query->get('callback');
        $citys = $this->getCityService()->findCityByProvinceId($province_id);
        foreach ($citys as $city) {
            $data[] = array('id' => $city['id'],  'name' => $city['name'] );
        }
        return new JsonResponse($data);
    }

    public function newMatchAction(Request $request)
    {
        $data = array();
        $queryString = $request->query->get('q');
        $callback = $request->query->get('callback');
        $tags = $this->getLevelService()->getTagByLikeName($queryString);
        //$tags = $this->getTagService()->getTagByLikeName($queryString);
        foreach ($tags as $tag) {
            $data[] = array('id' => $tag['id'],  'name' => $tag['name'] );
        }
        return new JsonResponse($data);
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Level.LevelService');
    }

    protected function getProvinceService()
    {
        return $this->getServiceKernel()->createService('Province.ProvinceService');
    }

    protected function getCityService()
    {
        return $this->getServiceKernel()->createService('City.CityService');
    }

}