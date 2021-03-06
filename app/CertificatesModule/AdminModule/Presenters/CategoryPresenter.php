<?php
namespace CertificatesModule\AdminModule;

use CertificatesModule\AdminModule\Components\CategoryTable,
	CertificatesModule\AdminModule\Forms\CategoryForm,
	CertificatesModule\Models\Category\CategoryEntity,
	Nette\Forms\Controls\SubmitButton;

class CategoryPresenter extends \Presenters\BasePresenter
{
	/**
	 * @autowire(CertificatesModule\Models\Category\CategoryEntity,
	 * 	factory=Kdyby\Doctrine\EntityDaoFactory)
	 * @var \Kdyby\Doctrine\EntityDao
	 */
	protected $categoryDao;


	public function actionAdd()
	{
		$this->setView('edit');

		$categoryForm = $this['categoryForm'];
		$categoryForm['save']->onClick[] = callback($this, 'addCategory');
	}

	/**
	 * @param SubmitButton $button
	 */
	public function addCategory(SubmitButton $button)
	{
		$values = $button->getForm()->getValues();

		$categoryEntity = new CategoryEntity($values->name, $values->codePrefix);
		$categoryEntity->setDescription($values->description);

		$this->categoryDao->save($categoryEntity);

		$this->flashMessage('Kategória certifikátov bola úspešne pridaná.', 'success');
		$this->redirect('list');
	}

	/**
	 * @var int $id
	 */
	public function actionEdit($id)
	{
		$categoryEntity = $this->categoryDao->find($id);

		if (!$categoryEntity)
		{
			throw new \Nette\Application\BadRequestException('Category not found.');
		}

		$categoryForm = $this['categoryForm'];
		$categoryForm->bindEntity($categoryEntity);
		$categoryForm['save']->onClick[] = callback($this, 'editCategory');
	}

	/**
	 * @param SubmitButton $button
	 */
	public function editCategory(SubmitButton $button)
	{
		$values = $button->getForm()->getValues();
		$categoryEntity = $button->getForm()->getEntity()
			->setName($values->name)
			->setCodePrefix($values->codePrefix)
			->setDescription($values->description);

		$this->categoryDao->save($categoryEntity);

		$this->flashMessage('Kategória certifikátov bola úspešne upravená.', 'success');
		$this->redirect('list');
	}

	/**
	 * @return CategoryForm
	 */
	protected function createComponentCategoryForm()
	{
		return new CategoryForm($this->categoryDao);
	}

	/**
	 * @return CategoryTable
	 */
	protected function createComponentCategoryTable()
	{
		$queryBuilder = $this->categoryDao->createQueryBuilder('category')
			->leftJoin('category.certificateTypes', 'certificateTypes')
			->groupBy('category.id');

		return new CategoryTable($this->categoryDao, $queryBuilder);
	}
}