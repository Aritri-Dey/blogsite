<?php 

/**
 * @file
 * Generates markup to be displayed. Functionality in this Controller 
 * is wired to drupal in otp_module.routing.yml 
 */

namespace Drupal\otp_module\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Peast\Formatter\PrettyPrint;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\node\Entity\Node;



/**
 * Class to implement controller of mymodule.
 */
class OtpController extends ControllerBase {

   /**
   * Stores the instance of Entity Type Manager Interface.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  protected $connection;


   /**
   * Initializes the object to class variables.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Stores the instance of Entity Type Manager Interface.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, Connection $connection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->connection = $connection;
  }
  
    /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('database'),
    );
  }

  /**
   * Generates the json response of 'blogs' nodes by specific node id.
   * 
   *   @param \Symfony\Component\HttpFoundation\Request $request
   *     Stores the object of Request Class.
   *
   *   @return \Symfony\Component\HttpFoundation\JsonResponse
   *     The data of the blogs nodes.
   */
  public function getNodeData(Request $request) {
    $node_id = $request->query->get('id');
    // $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'blogs']);
    if ($node_id) {
      // $node = Node::load($node_id);
      $node = $this->entityTypeManager->getStorage('node')->load($node_id);
      if ($node) {
        // dd()
        $node_data['data'][] = [
          'blog_title' => $node->getTitle(),
          'blog_uuid' =>$node->get('uuid')->getValue()[0]['value'],
          'blog_body' => $node->get('body')->getValue()[0]['value'],
          'field_published_date' => $node->get('field_published_date')->getValue()[0]['value'],
        ];
        // dd($node_data);
        return new JsonResponse($node_data);
      }
      return new JsonResponse(['error' => 'Node not found.'], 404);
    }
    return new JsonResponse(['error' => 'Node ID does not exist.'], 404);
  }

  /**
   * Generates the json response of all 'blogs' nodes.
   *
   *   @return \Symfony\Component\HttpFoundation\JsonResponse
   *     The data of the blogs nodes.
   */
  public function getAllNodes() {
    
    // $database = \Drupal::database();
    $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'blogs']);
    
    foreach ($nodes as $node) {
      $uid = $node->getOwnerId();
      $author = $this->entityTypeManager->getStorage('user')->load($uid);
      // dd($author->get('name')->getValue()[0]['value']);
      // $user = user_load($uid);
      // dd($node->get('field_blog_tags')->getValue());
      // dd($node);

      // Getting the name of the tags related to the blog via target_id.
      foreach ($node->get('field_blog_tags')->getValue() as $tid) {
        $tag = $tid['target_id'];
        $query = $this->connection->select('taxonomy_term_field_data', 't');
        $query->fields('t', ['tid', 'name'])
              ->condition('t.tid', $tag, '=');
        $result = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($result as $record) {
          $taxo_term = $record['name'];
        }
        $tags[] = $taxo_term;
      }
      // dd($nodes);
      // $node = \Drupal\node\Entity\Node::load($node_id);
    
      $node_data['data'][] = [
        'blog_title' => $node->getTitle(),
        // 'event_uuid' =>$node->get('uuid')->getValue()[0]['value'],
        'blog_body' => $node->get('body')->getValue()[0]['value'],
        // 'blog_author' => $node->get('author')->getValue(),
        'field_published_date' => $node->get('field_published_date')->getValue()[0]['value'],
        'blog_tags' => $tags,
        'blog_author' => $author->get('name')->getValue()[0]['value'],
      ];
      // dd($node_data);
    }
    return new JsonResponse($node_data);
  }

  /**
   * Generates the json response of 'blogs' nodes by specific author/tag.
   * 
   *   @param \Symfony\Component\HttpFoundation\Request $request
   *     Stores the object of Request Class.
   *
   *   @return \Symfony\Component\HttpFoundation\JsonResponse
   *     The data of the blogs nodes.
   */
  public function getSpecificNode(Request $request) {
    $author = $request->query->get('author');
    $tag = $request->query->get('tag');
    // Checking for author. 
    if ($author) {
      // Getting the user id from the author name entered.
      $query = $this->entityTypeManager->getStorage('user')->getQuery()
               ->condition('name', $author)
               ->range(0, 1) // Adding range since usernames should be unique.
               ->accessCheck(TRUE);
      $results = $query->execute();
      foreach ($results as $result) {
        $author_id = $result;
      }
      // Getting the 'blogs' nodes created by that particular user.
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
              ->condition('type', 'blogs')
              ->condition('uid', $author_id)
              ->accessCheck(TRUE);
      $nids = $query->execute();
      // Loading nodes created by that user.
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      // dd($nodes);
      foreach ($nodes as $node) {
        // Getting tags related to the node by calling the getTags() method.
        $tags = $this->getTags($node);
        $node_data['data'][] = [
          'blog_title' => $node->getTitle(),
          'blog_body' => $node->get('body')->getValue()[0]['value'],
          'blog_author' => $author,
          'blog_tags' => $tags,
          'field_published_date' => $node->get('field_published_date')->getValue()[0]['value'],
        ];

      }
      // dd($node_data);
      return new JsonResponse($node_data);
    }

    // Checking for tags.
    if ($tag) {
      // Fetching the id of the taxo term using entityQuery.
      $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
              ->condition('name', $tag)
              ->range(0, 1) // Add a range since term names should be unique.
              ->accessCheck(TRUE);

      $results = $query->execute();
      foreach ($results as $result) {
        $tid = $result;
      }
      // Getting the nodes that are associated with that taxonomy term id.
      $query = $this->entityTypeManager->getStorage('node')->getQuery()
                ->condition('type', 'blogs')
                ->condition('field_blog_tags.target_id', $tid) 
                ->accessCheck(TRUE);
      $nids = $query->execute();
      // Load the nodes.
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
      foreach ($nodes as $node) {
        // Getting tags related to the node by calling the getTags() method.
        $tags = $this->getTags($node);
        // Getting the author of the node.
        $uid = $node->getOwnerId();
        $author = $this->entityTypeManager->getStorage('user')->load($uid);
        $node_data['data'][] = [
          'blog_title' => $node->getTitle(),
          'blog_body' => $node->get('body')->getValue()[0]['value'],
          'blog_author' => $author->get('name')->getValue()[0]['value'],
          'blog_tags' => $tags,
          'field_published_date' => $node->get('field_published_date')->getValue()[0]['value'],
        ];

      }
      return new JsonResponse($node_data);
    }

    else {
      $nodes = $this->entityTypeManager->getStorage('node')->loadByProperties(['type' => 'blogs']);
      foreach ($nodes as $node) {
        // Getting tags related to the node by calling the getTags() method.
        $tags = $this->getTags($node);
        $uid = $node->getOwnerId();
        $author = $this->entityTypeManager->getStorage('user')->load($uid);
        $node_data['data'][] = [
          'blog_title' => $node->getTitle(),
          'blog_body' => $node->get('body')->getValue()[0]['value'],
          'field_published_date' => $node->get('field_published_date')->getValue()[0]['value'],
          'blog_tags' => $tags,
          'blog_author' => $author->get('name')->getValue()[0]['value'],
        ];
      }
      return new JsonResponse($node_data);
    }
  }

  /**
   * Function to get the tag names related to the node.
   * 
   *  @param $node
   *    The node for which the tags are being fetched.
   *  @return array
   *    Returns the tags related to the node.
   */
  public function getTags($node) {
    // Fetching the taxonomy term ids related to the node.
    foreach ($node->get('field_blog_tags')->getValue() as $tid) {
      $tag = $tid['target_id'];
      $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery()
          ->condition('tid', $tag)
          ->range(0, 1) 
          ->accessCheck(TRUE);
      $result = $query->execute();
      // Fetching the name via tid.
      foreach ($result as $record) {
        $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($record);
        $taxo_term = $term->getName();
      }
      $tags[] = $taxo_term;
    }
    return $tags;
  }

}
