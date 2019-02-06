<?php

namespace App\Entity\Assessment;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Class FormCategory
 *
 * @ORM\Entity(repositoryClass="App\Repository\Assessment\FormCategoryRepository")
 * @ORM\Table(name="tbl_assessment_form_category")
 */
class FormCategory
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({
     *     "api_admin_assessment_form_list",
     *     "api_admin_assessment_form_get",
     *     "api_admin_resident_assessment_list",
     *     "api_admin_resident_assessment_get",
     *     "api_admin_resident_assessment_report"
     * })
     */
    private $id;

    /**
     * @var int
     * @ORM\Column(name="order_number", type="integer", nullable=false)
     * @Assert\GreaterThan(0, groups={
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get"
     * })
     * @Groups({
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $orderNumber = 0;

    /**
     * @var Category
     * @ORM\ManyToOne(targetEntity="Category", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_category", referencedColumnName="id", onDelete="CASCADE")
     * })
     * @Groups({
     *      "api_admin_assessment_form_list",
     *      "api_admin_assessment_form_get",
     *      "api_admin_resident_assessment_list",
     *      "api_admin_resident_assessment_get",
     *      "api_admin_resident_assessment_report"
     * })
     */
    private $category;

    /**
     * @var Form
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="formCategories", cascade={"persist"})
     * @ORM\JoinColumns({
     *      @ORM\JoinColumn(name="id_form", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    private $form;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber(int $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory(Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * @param Form $form
     */
    public function setForm(Form $form): void
    {
        $this->form = $form;
    }
}
