<?php

namespace app\modules\admin\controllers;

use app\models\Staff;
use app\modules\admin\forms\StaffForm;
use app\modules\admin\searches\StaffSearch;
use app\rbac\Rbac;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * StaffController implements the CRUD actions for Staff model.
 */
class StaffController extends BaseAdminController
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'delete' => ['POST'],
                        'deactivate' => ['POST'],
                        'activate' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Staff model.
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Создание сотрудника
     * @return string|Response
     */
    public function actionCreate(): Response|string
    {
        $model = new Staff();
        $model->loadDefaultValues();

        $form = new StaffForm($model, config: ['scenario' => StaffForm::SCENARIO_CREATE]);

        if ($this->request->isPost) {
            if ($form->load($this->request->post()) && ($model = $form->save()) !== null) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'form' => $form,
        ]);
    }

    /**
     * Редактирование сотрудника
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException
     * @throws ForbiddenHttpException
     */
    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);
        $form = new StaffForm($model, ['scenario' => StaffForm::SCENARIO_UPDATE]);

        if ($this->request->isPost) {
            if (Yii::$app->user->getId() === $id) {
                throw new ForbiddenHttpException('Вы не можете изменять себя');
            }
            if ($form->load($this->request->post()) && $form->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'form' => $form,
            'model' => $model,
        ]);
    }

    /**
     * Программное удаление сотрудника
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если запись не найдена
     * @throws ForbiddenHttpException если действие недоступно
     */
    public function actionDelete(int $id): Response
    {
        if (Yii::$app->user->getId() === $id) {
            throw new ForbiddenHttpException('Вы не можете удалить себя');
        }

        $model = $this->findModel($id);

        if ($model->isLastActiveSuperadmin()) {
            throw new ForbiddenHttpException('Нельзя удалить последнего активного суперадмина');
        }

        $model->softDelete();

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionDeactivate(int $id): Response
    {
        if (Yii::$app->user->getId() === $id) {
            throw new ForbiddenHttpException('Вы не можете деактивировать себя');
        }

        $model = $this->findModel($id);

        if ($model->isLastActiveSuperadmin()) {
            throw new ForbiddenHttpException('Нельзя деактивировать последнего активного суперадмина');
        }

        $model->deactivate();

        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Response
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionActivate(int $id): Response
    {
        if (Yii::$app->user->getId() === $id) {
            throw new ForbiddenHttpException('Вы не можете активировать себя');
        }

        $this->findModel($id)->activate();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Staff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id
     * @return Staff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Staff
    {
        if (($model = Staff::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    protected function accessRules(): array
    {
        return [
            [
                'allow' => true,
                'permissions' => [Rbac::PERM_MANAGE_STAFF],
            ],
        ];
    }
}
