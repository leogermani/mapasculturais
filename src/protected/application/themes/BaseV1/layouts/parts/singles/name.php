<?php
$class = isset($disable_editable) ? '' : 'js-editable';

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
?>

<?php $this->applyTemplateHook('name','before'); ?>
<h2><span class="<?php echo $class ?> <?php echo ($entity->isPropertyRequired($entity,"name") && $editEntity? 'required': '');?>" data-edit="name" data-original-title="Nome de exibição" data-emptytext="Nome de exibição"><?php echo $entity->name; ?></span></h2>
<?php $this->applyTemplateHook('name','after'); ?>
