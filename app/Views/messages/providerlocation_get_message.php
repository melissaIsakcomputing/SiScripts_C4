<?php header ("Content-Type:text/xml"); ?>
<?php echo '<?xml version="1.0" encoding="UTF-8"?>';?>
<DirectoryMessage xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" DatatypesVersion="20170715" TransportVersion="20170715" TransactionDomain="DIRECTORY" TransactionVersion="20170715" StructuresVersion="20170715" ECLVersion="20170715" Version="006" Release="001">
    <Header>
        <To Qualifier="ZZZ">SSDR61</To>
        <From Qualifier="ZZZ"><?=$From?></From>
        <MessageID><?=$MessageID?></MessageID>
        <SentTime><?=$SentTime?></SentTime>
        <SenderSoftware>
            <SenderSoftwareDeveloper>Isak Computing, LLC</SenderSoftwareDeveloper>
            <SenderSoftwareProduct>SiCompound</SenderSoftwareProduct>
            <SenderSoftwareVersionRelease>1.0</SenderSoftwareVersionRelease>
        </SenderSoftware>
    </Header>
    <Body>
    <GetProviderLocation>
        <ProviderLocation>
            <Identification>
                <SPI><?=$SPI?></SPI>
            </Identification>
        </ProviderLocation>
    </GetProviderLocation>
    </Body>
</DirectoryMessage>