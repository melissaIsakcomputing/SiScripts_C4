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
    <UpdateProviderLocation>
        <DirectoryInformation>
            <AccountID><?=$AccountID?></AccountID>
            <PortalID><?=$PortalID?></PortalID>
            <ActiveStartTime>2018-02-14T15:23:12.215321Z</ActiveStartTime>
            <ActiveEndTime>2018-03-16T15:23:12.215321Z</ActiveEndTime>
            <ServiceLevels>
            <?php if (isset($ServiceLevels)) {
                foreach ($ServiceLevels as $level) { ?>
                <ServiceLevel>
                    <ServiceLevelName><?=$level?></ServiceLevelName>
                </ServiceLevel>
            <?php }
            }
            ?>
            </ServiceLevels>
            <UseCases>
                <UseCase>
                    <UseCaseCode>UCTest1</UseCaseCode>
                </UseCase>
            </UseCases>
        </DirectoryInformation>
        <ProviderLocation>
            <Identification>
                <NPI><?=$NPI?></NPI>
                <SPI><?=$SPI?></SPI>
            </Identification>
            <Name>
                <LastName><?=$LastName?></LastName>
                <FirstName><?=$FirstName?></FirstName>
            </Name>
            <Address>
                <AddressLine1><?=AddressLine1?></AddressLine1>
                <City><?=$City?></City>
                <StateProvince><?=$StateProvince?></StateProvince>
                <PostalCode><?=$PostalCode?></PostalCode>
                <CountryCode><?=$CountryCode?></CountryCode>
            </Address>
            <CommunicationNumbers>
                <PrimaryTelephone>
                    <Number><?=$PrimaryTelephone?></Number>
                </PrimaryTelephone>
                <Fax>
                    <Number><?=$Fax?></Number>
                </Fax>
            </CommunicationNumbers>
        </ProviderLocation>
    </UpdateProviderLocation>
    </Body>
</DirectoryMessage>