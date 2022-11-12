<?php

namespace App\Entity\Admin;

use App\Entity\CommonTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Tree\Node as GedmoNode;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Table(name="admin_menu")
 * @ORM\Entity(repositoryClass="Gedmo\Tree\Entity\Repository\NestedTreeRepository")
 */
class Menu implements GedmoNode
{
  use CommonTrait;

  /**
   * @ORM\Column(name="id", type="integer")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="AUTO")
   */
  private $id;

  /**
   * @ORM\Column("menu_label", type="string", length=64)
   */
  private $label;

  /**
   * @ORM\Column("menu_uri", type="string", length=64)
   */
  private $uri;

  /**
   * 菜单图标
   * @var string
   * 
   * @ORM\Column("icon", length=255, nullable=true)
   */
  private $icon;

  /**
   * @Gedmo\TreeLeft
   * @ORM\Column(name="lft", type="integer")
   */
  private $lft;

  /**
   * @Gedmo\TreeLevel
   * @ORM\Column(name="lvl", type="integer")
   */
  private $lvl;

  /**
   * @Gedmo\TreeRight
   * @ORM\Column(name="rgt", type="integer")
   */
  private $rgt;

  /**
   * @Gedmo\TreeRoot
   * @ORM\ManyToOne(targetEntity="Menu")
   * @ORM\JoinColumn(name="tree_root", referencedColumnName="id", onDelete="CASCADE")
   */
  private $root;

  /**
   * @Gedmo\TreeParent
   * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children")
   * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="CASCADE")
   */
  private $parent;

  /**
   * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent")
   * @ORM\OrderBy({"lft" = "ASC"})
   */
  private $children;

  /**
   * Get the value of id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set the value of id
   *
   * @return  self
   */
  public function setId($id)
  {
    $this->id = $id;

    return $this;
  }

  /**
   * Get the value of label
   */
  public function getLabel()
  {
    return $this->label;
  }

  /**
   * Set the value of label
   * 
   * @return self
   */
  public function setLabel($label)
  {
    $this->label = $label;

    return $this;
  }

    /**
   * Get the value of icon
   */
  public function getIcon()
  {
    return $this->icon;
  }

  /**
   * Set the value of icon
   * 
   * @return self
   */
  public function setIcon($icon)
  {
    $this->icon = $icon;

    return $this;
  }

    /**
   * Get the value of uri
   */
  public function getUri()
  {
    return $this->uri;
  }

  /**
   * Set the value of uri
   * 
   * @return self
   */
  public function setUri($uri)
  {
    $this->uri = $uri;

    return $this;
  }

  /**
   * Get the value of lft
   */
  public function getLft()
  {
    return $this->lft;
  }

  /**
   * Set the value of lft
   *
   * @return  self
   */
  public function setLft($lft)
  {
    $this->lft = $lft;

    return $this;
  }

  /**
   * Get the value of lvl
   */
  public function getLvl()
  {
    return $this->lvl;
  }

  /**
   * Set the value of lvl
   *
   * @return  self
   */
  public function setLvl($lvl)
  {
    $this->lvl = $lvl;

    return $this;
  }

  /**
   * Get the value of rgt
   */
  public function getRgt()
  {
    return $this->rgt;
  }

  /**
   * Set the value of rgt
   *
   * @return  self
   */
  public function setRgt($rgt)
  {
    $this->rgt = $rgt;

    return $this;
  }

  /**
   * Get the value of root
   */
  public function getRoot()
  {
    return $this->root;
  }

  /**
   * Set the value of root
   *
   * @return  self
   */
  public function setRoot($root)
  {
    $this->root = $root;

    return $this;
  }

  /**
   * Get the value of parent
   */
  public function getParent()
  {
    return $this->parent;
  }

  /**
   * Set the value of parent
   *
   * @return  self
   */
  public function setParent($parent)
  {
    $this->parent = $parent;

    return $this;
  }

  /**
   * Get the value of children
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Set the value of children
   *
   * @return  self
   */
  public function setChildren($children)
  {
    $this->children = $children;

    return $this;
  }
}
