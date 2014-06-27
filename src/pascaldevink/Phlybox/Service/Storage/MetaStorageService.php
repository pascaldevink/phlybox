<?php

namespace pascaldevink\Phlybox\Service\Storage;

interface MetaStorageService 
{
    /**
     * Adds a box to the meta storage and returns the identifier.
     *
     * @param string $name
     * @param string $repositoryOwner
     * @param string $repositoryName
     * @param string $branch
     * @param int $prNumber
     *
     * @return int
     */
    public function addBox($name, $repositoryOwner, $repositoryName, $branch, $prNumber);

    /**
     * Sets the status of the box with the given id.
     * Status can be any of the constants in this interface.
     *
     * @param int $boxId
     * @param string $status
     *
     * @return void
     */
    public function setBoxStatus($boxId, $status);

    /**
     * Removes the box from the meta storage.
     *
     * @param int $boxId
     *
     * @return void
     */
    public function removeBox($boxId);

    /**
     * Returns a list of all boxes.
     *
     * @return array
     */
    public function getAllBoxes();

    /**
     * Returns the box with the given identifier.
     * If the box does not exist, an exception is thrown.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return array
     */
    public function getBoxByIdentifier($id);
} 