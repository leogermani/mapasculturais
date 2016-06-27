<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.project';

$project = $entity->project;

$this->addEntityToJs($entity);

$this->addRegistrationToJs($entity);

$this->includeAngularEntityAssets($entity);

$owner 			= isset($project->registrationSeals->owner)?$project->registrationSeals->owner:'';
$institution	= isset($project->registrationSeals->institution)?$project->registrationSeals->institution:'';
$collective		= isset($project->registrationSeals->collective)?$project->registrationSeals->collective:'';

$this->addSealsToJs(false,[$owner,$institution,$collective]);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content registration" ng-controller="ProjectController">
    <?php $this->part('singles/registration--header', $_params); ?>
    
    <article>
        <?php $this->applyTemplateHook('form','begin'); ?>
        
        <?php $this->part('singles/registration-edit--header', $_params) ?>
        
        <?php $this->part('singles/registration-edit--categories', $_params) ?>
        
        <?php $this->part('singles/registration-edit--agents', $_params) ?>
        
        <?php $this->part('singles/registration-edit--fields', $_params) ?>
        
        <?php $this->part('singles/registration-edit--send-button', $_params) ?>

 	<!-- BEGIN Seals -->
	<div id="registration-agent" class="registration-fieldset">
		<h4>5. Selos Certificadores</h4>
		<p class="registration-help">Selos certificadores que serão atribuídos aos agentes da inscrição quando a mesma for aprovada.</p>
		<ul class="registration-list" ng-controller="SealsController">
			<li class="registration-list-item registration-edit-mode">
				<div class="registration-label">
					<span class="label">Agente responsável</span>
				</div>
				<div class="registration-description">Selos atribuídos a agentes</div>
				
				<div class="js-registration-agent registration-agent">
					<div class="clearfix">
						<div ng-if="<?php echo $owner;?>" class="avatar-agent-registration ng-scope">
							<img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $owner;?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
			                <div>
			                    <a href="{{seals[getArrIndexBySealId(<?php echo $owner;?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $owner;?>)].name}}</a>
			                    <span ng-if="!<?php echo $owner;?>">Não informado</span>
			                </div>
		            	</div>
					</div>
				</div>
			</li>
			<li class="registration-list-item registration-edit-mode">
				<div class="registration-label">
					<span class="label">Instituição responsável</span>
				</div>
				<div class="registration-description">Selos atribuídos a instituições</div>
				
				<div class="js-registration-agent registration-agent">
					<div class="clearfix">
						<div ng-if="<?php echo $institution;?>" class="avatar-agent-registration ng-scope">
							<img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $institution;?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
			                <div>
			                    <a href="{{seals[getArrIndexBySealId(<?php echo $institution;?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $institution;?>)].name}}</a>
			                    <span ng-if="!<?php echo $institution;?>">Não informado</span>
			                </div>
		            	</div>
					</div>                        
				</div>
			</li>
			<li class="registration-list-item registration-edit-mode">
				<div class="registration-label">
					<span class="label">Coletivo</span>
				</div>
				<div class="registration-description">Selos atribuídos a agentes coletivos</div>
				
				<div class="js-registration-agent registration-agent">
					<div class="clearfix">
		            	<div ng-if="<?php echo $collective;?>" class="avatar-seal-registration ng-scope">
							<img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $collective;?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
			                <div>
			                    <a href="{{seals[getArrIndexBySealId(<?php echo $collective;?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $collective;?>)].name}}</a>
			                    <span ng-if="'<?php echo $collective;?>' == ''">Não informado</span>
			                </div>
		            	</div>
					</div>
				</div>
			</li>
		</ul>
	</div>
	<!-- END Seals -->
 	
    <!-- anexos -->
    <div ng-if="data.entity.registrationFileConfigurations.length > 0" id="registration-attachments" class="registration-fieldset">
        <h4>Anexos (documentos necessários)</h4>
        <p class="registration-help">Para efetuar sua inscrição, faça upload dos documentos abaixo.</p>
        <ul class="attachment-list" ng-controller="RegistrationFilesController">
            <li ng-repeat="fileConfiguration in data.fileConfigurations" on-repeat-done="init-ajax-uploaders" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item registration-edit-mode">
                <div class="label"> {{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}</div>
                <div class="attachment-description">
                    {{fileConfiguration.description}}
                    <span ng-if="fileConfiguration.template">
                        (<a class="attachment-template" target="_blank" href="{{fileConfiguration.template.url}}">baixar modelo</a>)
                    </span>
                </div>
                <a ng-if="fileConfiguration.file" class="attachment-title" href="{{fileConfiguration.file.url}}" target="_blank">{{fileConfiguration.file.name}}</a>
                <?php if($this->isEditable()): ?>
                    <div class="btn-group">
                        <!-- se já subiu o arquivo-->
                        <!-- se não subiu ainda -->
                        <a class="btn btn-default hltip" ng-class="{'send':!fileConfiguration.file,'edit':fileConfiguration.file}" ng-click="openFileEditBox(fileConfiguration.id, $index, $event)" title="{{!fileConfiguration.file ? 'enviar' : 'editar'}} anexo">{{!fileConfiguration.file ? 'Enviar' : 'Editar'}}</a>
                        <a class="btn btn-default delete hltip" ng-if="!fileConfiguration.required && fileConfiguration.file" ng-click="removeFile(fileConfiguration.id, $index)" title="excluir anexo">Excluir</a>
                    </div>
                    <edit-box id="editbox-file-{{fileConfiguration.id}}" position="bottom" title="{{fileConfiguration.title}} {{fileConfiguration.required ? '*' : ''}}" cancel-label="Cancelar" close-on-cancel='true' on-submit="sendFile" submit-label="Enviar anexo" index="{{$index}}" spinner-condition="data.uploadSpinner">
                        <form class="js-ajax-upload" method="post" action="{{uploadUrl}}" data-group="{{fileConfiguration.groupName}}"  enctype="multipart/form-data">
                            <div class="alert danger hidden"></div>
                            <p class="form-help">Tamanho máximo do arquivo: {{maxUploadSizeFormatted}}</p>
                            <input type="file" name="{{fileConfiguration.groupName}}" />

                            <div class="js-ajax-upload-progress">
                                <div class="progress">
                                    <div class="bar"></div>
                                    <div class="percent">0%</div>
                                </div>
                            </div>
                        </form>
                    </edit-box>
                <?php endif;?>
            </li>
        </ul>
    </div>
    <div class="registration-fieldset">
                
        <?php if($entity->project->isRegistrationOpen()): ?>
            <p class="registration-help">Certifique-se que você preencheu as informações corretamente antes de enviar sua inscrição. <strong>Depois de enviada, não será mais possível editá-la.</strong></p>
            <a class="btn btn-primary" ng-click="sendRegistration()">Enviar inscrição</a>
        <?php else: ?>
            <p class="registration-help">
                <strong>
                    <?php // gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642
                    $date = strftime("%d de {%B} de %G às %H:%M", $entity->project->registrationTo->getTimestamp());
                    $full_date = preg_replace_callback("/{(.*?)}/", function($matches) use ($app) {
                        return strtolower($app::txt(str_replace(['{', '}'], ['',''], $matches[0]))); //removes curly brackets from the matched pattern and convert its content to lowercase
                    }, $date);
                    ?>
                    As inscrições encerraram-se em <?php echo $full_date; ?>.
                </strong>
            </p>
        <?php endif; ?>
            
        <?php if(!$entity->project->isRegistrationOpen() && $app->user->is('superAdmin')): ?>
            <a ng-click="sendRegistration()" class="btn btn-danger hltip" data-hltip-classes="hltip-danger" hltitle="Somente super admins podem usar este botão e somente deve ser usado para enviar inscrições que não foram enviadas por problema do sistema." data-status="<?php echo MapasCulturais\Entities\Registration::STATUS_SENT ?>">enviar esta inscrição</a>
        <?php endif ?>
    </div>
</article>
<?php $this->part('singles/registration--sidebar--left', $_params) ?>
<?php $this->part('singles/registration--sidebar--right', $_params) ?>
