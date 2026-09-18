<?php
$escapeXml = static function ($value): string {
    return htmlspecialchars(
        (string) ($value ?? ''),
        ENT_XML1 | ENT_QUOTES,
        'UTF-8'
    );
};

?>
<?= '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL ?>
<Message
        DatatypesVersion="<?= $escapeXml($DatatypesVersion ?? '20170715') ?>"
        TransportVersion="<?= $escapeXml($TransportVersion ?? '20170715') ?>"
        TransactionDomain="<?= $escapeXml($TransactionDomain ?? 'SCRIPT') ?>"
        TransactionVersion="<?= $escapeXml($TransactionVersion ?? '20170715') ?>"
        StructuresVersion="<?= $escapeXml($StructuresVersion ?? '20170715') ?>"
        ECLVersion="<?= $escapeXml($ECLVersion ?? '20170715') ?>">
    <Header>
        <To Qualifier="<?= $escapeXml($MessageToQ ?? '') ?>"><?= $escapeXml($MessageToID ?? '') ?></To>
        <From Qualifier="<?= $escapeXml($MessageFromQ ?? '') ?>"><?= $escapeXml($MessageFromID ?? '') ?></From>
        <MessageID><?= $escapeXml($MessageID ?? '') ?></MessageID>
        <RelatesToMessageID><?= $escapeXml($RelatesToMessageID ?? '') ?></RelatesToMessageID>
        <SentTime><?= $escapeXml($SentTime ?? date(DATE_ATOM)) ?></SentTime>

        <SenderSoftware>
            <SenderSoftwareDeveloper>Isak Computing, LLC</SenderSoftwareDeveloper>
            <SenderSoftwareProduct>SiCompound</SenderSoftwareProduct>
            <SenderSoftwareVersionRelease>1.0</SenderSoftwareVersionRelease>
        </SenderSoftware>
    </Header>

    <Body>
    <Status>
        <Code><?= $escapeXml($Code ?? '900') ?></Code>
    </Status>

    <?php if (!empty($dev)): ?>
        <Dev>
            <Msg><?= $escapeXml($dev) ?></Msg>

            <?php if (!empty($devCode)): ?>
                <Code><?= $escapeXml($devCode) ?></Code>
            <?php endif; ?>
        </Dev>
    <?php endif; ?>
    </Body>
</Message>