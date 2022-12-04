<?php
declare(strict_types=1);

/**
 * Created by Pavel Herink.
 * Date: 2019-03-16
 */

namespace Pavher\Sdao\Tests;

use Pavher\Sdao\Tests\_files\JsonEntity;
use Pavher\Sdao\Tests\_files\JsonWrapper;
use PHPUnit\Framework\TestCase;

class JsonEntityTest extends TestCase
{
    public function testCreateJsonEntityWrapper(): void
    {
        $data = [
            'id_json_wrapper' => 15,
            'json_entity' => '{
                "id_json": 30,
                "json_string": "Json test string.",
                "json_int": 45,
                "json_boolean": true,
                "json_date": {"date":"2019-03-16 01:10:05.000000","timezone_type":3,"timezone":"UTC"}
            }'
        ];

        $jsonWrapperEntity = new JsonWrapper($data);

        $jsonEntity = $jsonWrapperEntity->json_entity;

        $this->assertEquals(30, $jsonEntity->id_json);
        $this->assertEquals("Json test string.", $jsonEntity->json_string);
        $this->assertEquals(45, $jsonEntity->json_int);
        $this->assertEquals(true, $jsonEntity->json_boolean);
        $this->assertEquals(new \DateTime("2019-03-16 01:10:05"), $jsonEntity->json_date);
    }

    public function testSetToJsonEntityWrapper(): void
    {
        $data = ["id_json" => 30,
                "json_string" => "Json test string 2.",
                "json_int" => 45,
                "json_boolean" => true,
                "json_date" => "2019-03-15 02:10:55"];

        $jsonEntity = new JsonEntity($data);

        $jsonWrapperEntity = JsonWrapper::createFromArray(["id_json_wrapper" => 17]);
        $jsonWrapperEntity->json_entity = $jsonEntity;

        $this->assertEquals(17, $jsonWrapperEntity->id_json_wrapper);
        $this->assertEquals(new \DateTime("2019-03-15 02:10:55"), $jsonEntity->json_date);

        $this->assertEquals("Json test string 2.", $jsonWrapperEntity->json_entity->json_string);
    }

    public function testConvertJsonEntityPropertyToString(): void
    {
        $data = ["id_json" => 30,
            "json_string" => "Json test string 2.",
            "json_int" => 45,
            "json_boolean" => true,
            "json_date" => "2019-03-16T01:10:05+00:00"];

        $jsonEntity = new JsonEntity($data);

        $jsonWrapperEntity = new JsonWrapper(["id_json_wrapper" => 17, "json_entity" => $jsonEntity]);

        //die(var_dump($jsonWrapperEntity->asArray(null, true)));
        $this->assertEquals([
            "id_json_wrapper" => 17,
            "json_entity" => '{"id_json":30,"json_string":"Json test string 2.","json_int":45,"json_boolean":true,"json_date":"2019-03-16T01:10:05+00:00"}'], $jsonWrapperEntity->asArray(null, true));
    }


}