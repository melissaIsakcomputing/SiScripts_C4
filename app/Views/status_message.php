<?='<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL ?>
<Message
        DatatypesVersion="<?= $DatatypesVersion ?? '20170715'?>"
        TransportVersion="<?= $TransportVersion ?? '20170715' ?>"
        TransactionDomain="<?= $TransactionDomain ?? 'SCRIPT' ?>"
        TransactionVersion="<?= $TransactionVersion ?? '20170715' ?>"
        StructuresVersion="<?= $StructuresVersion ?? '20170715' ?>"
        ECLVersion="<?= $ECLVersion ?? '20170715' ?>">
    <Header>
        <To Qualifier="<?= $MessageToQ ?? '' ?>"><?= $MessageToID ?? ''?></To>
        <From Qualifier="<?= $MessageFromQ ?? '' ?>"><?= $MessageFromID ?? '' ?></From>
        <MessageID><?= $MessageID ?? ''?></MessageID>
        <RelatesToMessageID><?= $RelatesToMessageID ?? '' ?></RelatesToMessageID>
        <SentTime><?= $SentTime ?? date(DATE_ATOM) ?></SentTime>

        <SenderSoftware>
            <SenderSoftwareDeveloper>Isak Computing, LLC</SenderSoftwareDeveloper>
            <SenderSoftwareProduct>SiCompound</SenderSoftwareProduct>
            <SenderSoftwareVersionRelease>1.0</SenderSoftwareVersionRelease>
        </SenderSoftware>
    </Header>
    <Body>
    <Status>
        <Code><?= $Code ?? '900' ?></Code>
    </Status>
    </Body>
</Message>