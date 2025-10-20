<?php

use App\Model\People;



it('testing the function to count people it should return an integer', function () {
    $people = new People();
    $result = $people->countPeople();

    expect($result)->toBeInt();
});
