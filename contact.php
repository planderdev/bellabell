<?php
$page = 'contact';
require __DIR__ . '/includes/bootstrap.php';
require_once APP_ROOT . '/includes/contact-handler.php';
header('Cache-Control: no-store, max-age=0');
$errors=[];
$generalError='';
$success=($_GET['sent']??'')==='1';
$formData=['name'=>'','company'=>'','email'=>'','type'=>'','message'=>'','consent'=>''];
$ready=contact_ready();
$usedNonce=preg_match('/^[a-f0-9]{32}$/',(string)($_COOKIE['bellabell_contact_used']??''))?$_COOKIE['bellabell_contact_used']:'';
$clientIp=$_SERVER['REMOTE_ADDR']??'unknown';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $nonce=contact_token_nonce(contact_value($_POST,'csrf'));
    if ((int)($_SERVER['CONTENT_LENGTH']??0)>32768) {
        http_response_code(413);$generalError='securityError';
    } elseif (!valid_contact_token($_POST,null,$usedNonce)) {
        http_response_code(419);$generalError='securityError';
    } elseif (!contact_rate_allowed($clientIp,null,null,$nonce)) {
        http_response_code(429);header('Retry-After: 3600');$generalError='rateError';
    } else {
        foreach ($formData as $key=>$unused) $formData[$key]=contact_value($_POST,$key);
        $errors=validate_contact($formData);
        if ($errors) {http_response_code(422);}
        elseif (!$ready) {http_response_code(503);$generalError='notice';}
        else {
            $mailed=send_contact_email($formData,$lang);
            $stored=store_contact_enquiry($formData,$lang,$clientIp,$mailed);
            if ($mailed || $stored) {
                // Mark the token as used for this browser so a resubmit of the same form is rejected.
                setcookie('bellabell_contact_used',$nonce,['expires'=>time()+7200,'path'=>'/','secure'=>$isHttps,'httponly'=>true,'samesite'=>'Lax']);
                header('Location: '.url('contact').'?sent=1',true,303);exit;
            }
            http_response_code(502);$generalError='failed';
        }
    }
}
$csrfToken=issue_contact_token();
if ($csrfToken==='') { $ready=false; }
require APP_ROOT . '/includes/head.php';
require APP_ROOT . '/includes/header.php';
?>
<main id="main">
<?php require APP_ROOT . '/includes/page-hero.php'; ?>
<section class="contact-layout container">
    <div class="contact-aside"><h2><?= e(t('contact.heading')) ?></h2><p><?= e(t('contact.intro')) ?></p><div class="contact-direct"><p><?= e(t('contact.direct')) ?></p><a class="text-link" href="mailto:<?= e($company['email']) ?>"><?= e($company['email']) ?><?= icon('arrow-up-right') ?></a></div></div>
    <div>
        <?php if ($success): ?><div class="form-success" role="status"><?= e(t('contact.success')) ?></div><?php endif; ?>
        <?php if (!$ready): ?><div class="contact-notice" role="status"><?= e(t('contact.notice')) ?></div><?php endif; ?>
        <?php if ($errors || $generalError): ?><div class="form-errors" role="alert" tabindex="-1"><p><?= e(t('contact.' . ($generalError ?: 'invalid'))) ?></p><?php if ($errors): ?><ul><?php foreach ($errors as $field=>$error): ?><li><a href="#field-<?= e($field) ?>"><?= e(t('contact.'.$error)) ?></a></li><?php endforeach; ?></ul><?php endif; ?></div><?php endif; ?>
        <form class="contact-form" action="<?= e(url('contact')) ?>" method="post">
            <input type="hidden" name="csrf" value="<?= e($csrfToken) ?>">
            <div class="trap-field" aria-hidden="true"><label for="website"><?= e(t('contact.trap')) ?></label><input type="text" name="website" id="website" tabindex="-1" autocomplete="off"></div>
            <h3 class="form-section-title form-wide"><?= e(t('detail.contact.information')) ?></h3>
            <?php foreach (['name'=>['text','name',80,true],'company'=>['text','organization',120,false],'email'=>['email','email',254,true]] as $field=>$settings): ?>
            <div class="field form-field <?= $field==='email'?'form-wide':'' ?>"><label class="label" for="field-<?= e($field) ?>"><?= e(t('contact.'.$field)) ?><?= $settings[3]?'<span aria-hidden="true">*</span>':'' ?></label><input class="input" type="<?= e($settings[0]) ?>" id="field-<?= e($field) ?>" name="<?= e($field) ?>" autocomplete="<?= e($settings[1]) ?>" maxlength="<?= $settings[2] ?>" <?= $settings[3]?'required':'' ?> value="<?= e($formData[$field]) ?>" <?= isset($errors[$field])?'aria-invalid="true" aria-describedby="error-'.e($field).'"':'' ?>><?php if(isset($errors[$field])):?><p id="error-<?= e($field) ?>" class="field-error"><?= e(t('contact.'.$errors[$field])) ?></p><?php endif;?></div>
            <?php endforeach; ?>
            <h3 class="form-section-title form-wide"><?= e(t('detail.contact.project')) ?></h3>
            <div class="field form-field form-wide"><label class="label" for="field-type"><?= e(t('contact.type')) ?><span aria-hidden="true">*</span></label><select class="select" id="field-type" name="type" required <?= isset($errors['type'])?'aria-invalid="true" aria-describedby="error-type"':'' ?>><option value=""><?= e(t('contact.select')) ?></option><?php foreach (['distribution','partnership','global','other'] as $type): ?><option value="<?= e($type) ?>" <?= $formData['type']===$type?'selected':'' ?>><?= e(t('contact.typeOptions.'.$type)) ?></option><?php endforeach;?></select><?php if(isset($errors['type'])):?><p id="error-type" class="field-error"><?= e(t('contact.typeError')) ?></p><?php endif;?></div>
            <div class="field form-field form-wide"><label class="label" for="field-message"><?= e(t('contact.message')) ?><span aria-hidden="true">*</span></label><textarea class="textarea" id="field-message" name="message" rows="7" required minlength="20" maxlength="5000" aria-describedby="message-help<?= isset($errors['message'])?' error-message':'' ?>" <?= isset($errors['message'])?'aria-invalid="true"':'' ?>><?= e($formData['message']) ?></textarea><p id="message-help" class="form-help"><?= e(t('contact.messageHelp')) ?></p><?php if(isset($errors['message'])):?><p id="error-message" class="field-error"><?= e(t('contact.messageError')) ?></p><?php endif;?></div>
            <h3 class="form-section-title form-wide"><?= e(t('detail.contact.consent')) ?></h3>
            <div class="form-wide"><label class="checkbox consent-label" for="field-consent"><input type="checkbox" name="consent" id="field-consent" value="1" required <?= $formData['consent']==='1'?'checked':'' ?> <?= isset($errors['consent'])?'aria-invalid="true" aria-describedby="error-consent"':'' ?>><span><?= e(t('contact.consent')) ?></span></label><a class="consent-link" href="<?= e(url('privacy')) ?>" target="_blank" rel="noopener"><?= e(t('contact.privacyLink')) ?><span class="sr-only"><?= e(t('common.external')) ?></span></a><?php if(isset($errors['consent'])):?><p id="error-consent" class="field-error"><?= e(t('contact.consentError')) ?></p><?php endif;?></div>
            <div class="form-submit form-wide"><button class="button" type="submit" <?= !$ready?'disabled':'' ?> data-ready="<?= $ready?'true':'false' ?>" data-sending="<?= e(t('contact.sending')) ?>" data-original="<?= e(t('contact.submit')) ?>"><span><?= e(t('contact.submit')) ?></span><?= icon('arrow-up-right') ?></button><p class="form-help"><?= e(t('contact.requiredHelp')) ?></p></div>
        </form>
        <a class="text-link contact-faq-link" href="<?= e(url('faq')) ?>"><?= e(t('detail.contact.faqLink')) ?><?= icon('arrow-up-right') ?></a>
    </div>
</section>
</main>
<?php require APP_ROOT . '/includes/footer.php'; ?>
