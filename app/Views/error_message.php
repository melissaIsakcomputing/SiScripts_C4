<?php header ("Content-Type:text/xml"); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<!--<Message DatatypesVersion="2019071" TransportVersion="2019071" TransactionDomain="SCRIPT" TransactionVersion="2019071" StructuresVersion="2019071" ECLVersion="2019071" xsi:noNamespaceSchemaLocation="transport.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">-->
<Message DatatypesVersion="20170715" TransportVersion="20170715" TransactionDomain="SCRIPT" TransactionVersion="20170715" StructuresVersion="20170715" ECLVersion="20170715">
    <Header>
        <To Qualifier="<?=$MessageFromQ?>"><?=$MessageFromID?></To>
        <From Qualifier="<?=$MessageToQ?>"><?=$MessageToID?></From>
        <MessageID><?=$MessageID?></MessageID>
        <RelatesToMessageID><?=$RelatesToMessageID?></RelatesToMessageID>
        <SentTime><?=$SentTime?></SentTime>
        <SenderSoftware>
            <SenderSoftwareDeveloper>Isak Computing, LLC</SenderSoftwareDeveloper>
            <SenderSoftwareProduct>SiCompound</SenderSoftwareProduct>
            <SenderSoftwareVersionRelease>1.0</SenderSoftwareVersionRelease>
        </SenderSoftware>
    </Header>
    <Body>
        <Error>
            <Code></Code>
            <DescriptionCode></DescriptionCode>
            <Description></Description>
        </Error>
    </Body>
</Message>
