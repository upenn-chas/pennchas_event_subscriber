<?php 
$primary_domain = 'collegehouses.upenn.edu';

// host redirects
if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    switch ($host) {
        case 'fh.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/fisher-hassenfeld');
            exit();
        case 'gregory.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/gregory');
            exit();
        case 'gutmann.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/gutmann');
            exit();
        case 'harnwell.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/harnwell');
            exit();
        case 'harrison.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/harrison');
            exit();
        case 'hill.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/hill');
            exit();
        case 'kcech.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/kings-court-english');
            exit();
        case 'lauder.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/lauder');
            exit();
        case 'radian.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/radian');
            exit();
        case 'riepe.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/riepe');
            exit();
        case 'rodin.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/rodin');
            exit();
        case 'stouffer.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/stouffer');
            exit();
        case 'ware.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/ware');
            exit();
        case 'dubois.house.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . '/dubois');
            exit();
        case 'www.collegehouses.upenn.edu':
            header('HTTP/1.0 301 Moved Permanently');
            header('Location: https://' . $primary_domain . $_SERVER['REQUEST_URI']);
            exit();
    }
}

/* path examples from the flagship site
if (isset($_SERVER['REQUEST_URI'])) {
  $request = rtrim(urldecode($_SERVER['REQUEST_URI']), '/');
  switch ($request) {
    case '/penna-z':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /penn-a-z');
      exit();
    case '/highlights/visitors':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /visitors');
      exit();
    case '/highlights/parents':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /parents');
      exit();
    case '/services/hremp':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /services/human-resources-and-employment');
      exit();
    case '/services/event_planning':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /services/event-planning');
      exit();
    case '/about/comments':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /about/contact');
      exit();
    case '/about/trusteesadmin':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /about/trustees-and-administration');
      exit();
    case '/about/privacy_policy':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /about/privacy-policy');
      exit();
    case '/life-at-penn/sports':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /athletics-and-recreation');
      exit();
    case '/life-at-penn/athletics':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /athletics-and-recreation');
      exit();
    case '/life-at-penn/healthcare':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /life-at-penn/health-and-wellness');
      exit();
    case '/life-at-penn/groups_orgs':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /life-at-penn/groups-and-organizations');
      exit();    
    case '/life-at-penn/safety/emergency_preparedness':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /life-at-penn/safety/emergency-preparedness');
      exit();    
    case '/programs':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics');
      exit();
    case '/programs/undergraduate':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/undergraduate');
      exit();
    case '/programs/graduate':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/graduate');
      exit();
    case '/programs/academic-schools':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/schools');
      exit();
    case '/programs/lifelong':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/continuing-education');
      exit();
    case '/programs/distance':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/online-learning');
      exit();
    case '/programs/interschool':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/interdisciplinary');
      exit();
    case '/programs/international':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/global-initiatives');
      exit();
    case '/programs/acadsupport':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/resources');
      exit();
    case '/programs/undergrad-awards':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/awards');
      exit();
    case '/programs/offcampus':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/off-campus-learning');
      exit();
    case '/programs/undergrad-opps':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /academics/undergraduate-opportunities');
      exit();
    case '/life-at-penn/housing_dining':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /life-at-penn/housing-and-dining');
      exit();
    case '/researchdir':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /research-and-innovation');
      exit();
    case '/pages/valuing-grad-students';
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: https://valuing-grad-students.www.upenn.edu');
      exit();
    case '/pages/penn-honors-diversity';
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: https://penn-honors-diversity.www.upenn.edu');
      exit();
    case '/about/styleguide';
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: https://branding.web-resources.upenn.edu');
      exit();
    case '/pnc';
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /static/pnc');
      exit();
    case '/publictalk';
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: /static/publictalk');
      exit();
    case '/supporting-our-community':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: https://supporting-our-community.upenn.edu');
      exit();
    case ('/supporting-our-community/university-messages');
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: https://supporting-our-community.upenn.edu/university-messages');
      exit(); 
    case '/supporting-our-community/resources':
      header('HTTP/1.0 301 Moved Permanently');
      header('Location: https://supporting-our-community.upenn.edu/community-resources');
      exit();
    case '/autodiscover/autodiscover.xml':
      header("HTTP/1.0 403 Forbidden" );
      exit();
    case '/AutoDiscover/autodiscover.xml':
      header("HTTP/1.0 403 Forbidden" );
      exit();
  }
}
*/