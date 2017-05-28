<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContext;
use Topxia\Component\OAuthClient\OAuthClientFactory;

class LoginController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
             if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你已经登录了'), null, 3000, $this->generateUrl('admin'));
            }   
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你已经登录了'), null, 3000, $this->generateUrl('homepage'));
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($this->getWebExtension()->isMicroMessenger() && $this->setting('login_bind.enabled', 0) && $this->setting('login_bind.weixinmob_enabled', 0)) {
            $inviteCode = $request->query->get('inviteCode');
            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request), 'inviteCode' => $inviteCode)));
        }
        
        return $this->render('TopxiaWebBundle:Login:index.html.twig', array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            '_target_path'  => $this->getTargetPath($request)
        ));
    }

    public function ajaxAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:Login:ajax.html.twig', array(
            '_target_path' => $this->getTargetPath($request)
        ));
    }

    public function checkEmailAction(Request $request)
    {
        $email = $request->query->get('value');
        $user  = $this->getUserService()->getUserByEmail($email);

        if ($user) {
            $response = array('success' => true, 'message' => $this->getServiceKernel()->trans('该Email地址可以登录'));
        } else {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('该Email地址尚未注册'));
        }

        return $this->createJsonResponse($response);
    }

    public function oauth2LoginsBlockAction($targetPath, $displayName = true)
    {
        $clients = OAuthClientFactory::clients();
        return $this->render('TopxiaWebBundle:Login:oauth2-logins-block.html.twig', array(
            'clients'     => $clients,
            'targetPath'  => $targetPath,
            'displayName' => $displayName
        ));
    }

    protected function getTargetPath($request)
    {
        if ($request->query->get('goto')) {
            $targetPath = $request->query->get('goto');
        } elseif ($request->getSession()->has('_target_path')) {
            $targetPath = $request->getSession()->get('_target_path');
        } else {
            $targetPath = $request->headers->get('Referer');
        }

        if ($targetPath == $this->generateUrl('login', array(), true)) {
            return $this->generateUrl('homepage');
        }

        $url = explode('?', $targetPath);

        if ($url[0] == $this->generateUrl('partner_logout', array(), true)) {
            return $this->generateUrl('homepage');
        }

        if ($url[0] == $this->generateUrl('password_reset_update', array(), true)) {
            $targetPath = $this->generateUrl('homepage', array(), true);
        }

        if (strpos($targetPath, '/app.php') === 0) {
            $targetPath = str_replace('/app.php', '', $targetPath);
        }

        return $targetPath;
    }

    protected function getWebExtension()
    {
        return $this->container->get('topxia.twig.web_extension');
    }

    public function backgroundAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if ($user->isLogin()) {
            if (in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你已经登录了'), null, 3000, $this->generateUrl('admin'));
            }else if(in_array('ROLE_ADMIN', $user->getRoles())){
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你已经登录了'), null, 3000, $this->generateUrl('newadmin'));
            }else{
                return $this->createMessageResponse('info', $this->getServiceKernel()->trans('你已经登录了'), null, 3000, $this->generateUrl('homepage'));
            }
            
        }

        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $request->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
        }

        if ($this->getWebExtension()->isMicroMessenger() && $this->setting('login_bind.enabled', 0) && $this->setting('login_bind.weixinmob_enabled', 0)) {
            $inviteCode = $request->query->get('inviteCode');
            return $this->redirect($this->generateUrl('login_bind', array('type' => 'weixinmob', '_target_path' => $this->getTargetPath($request), 'inviteCode' => $inviteCode)));
        }
        $session = $request->getSession();
        $_target_path = 'newadmin';
        $session->set('_target_path', $_target_path);
        $path = $session->get('_target_path');

        $_target_path = $this->getTargetPath($request);
        return $this->render('TopxiaWebBundle:Login:newindex.html.twig', array(
            'last_username' => $request->getSession()->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            '_target_path'  => $_target_path
        ));

    }

    public function backgroundAdminAction(Request $request)
    {
          
        // $schools = $this->getSchoolsService()->findAllByNum(50);
        // if ($request->getMethod() == 'POST') {
        //        $username = $request->request->get('_username');
        //        $pwd = $request->request->get('_password');
        //        $school = $request->request->get('school_id');
        //        //return new Symfony\Component\HttpFoundation\RedirectResponse($this->generateUrl('newadmin'));
        //        return $this->redirect($this->generateUrl('newadmin'));
        //        //return $this->createJsonResponse($response);
        //     //    if ($form->isSubmitted() && $form->isValid()) {
        //     //     $content = $this->render('TopxiaAdminBundle:Default:newindex.html.twig');

        //     //     return $this->createJsonResponse(array('result' => true, 'html' => $content));
        //     //    } else {

        //     //    }
               
        //    }
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSchoolsService()
    {
        return $this->getServiceKernel()->createService('Schools.SchoolsService');
    }
}
