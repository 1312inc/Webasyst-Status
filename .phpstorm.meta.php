<?php

namespace PHPSTORM_META {
    override(\statusConfig::getEntityFactory(), map([
        '' => '@Factory'
    ]));
    override(\statusConfig::getModel(), map([
        '' => '@Model'
    ]));
    override(\statusConfig::getEntityRepository(), map([
        '' => '@Repository'
    ]));
}
