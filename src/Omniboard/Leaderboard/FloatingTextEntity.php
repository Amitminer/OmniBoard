<?php
/**
 * FloatingTextEntity Class
 * 
 * A custom entity implementation for displaying floating text in PocketMine-MP.
 * This entity appears as a floating text with no physical presence in the world.
 * 
 */
namespace Omniboard\Leaderboard;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;

/**
 * FloatingTextEntity implements a non-solid, non-interactive entity that displays text.
 * This entity visually appears as a nametag without an actual entity model.
 */
class FloatingTextEntity extends Entity {
    /**
     * Returns the network type ID for this entity.
     * Uses FALLING_BLOCK as the base entity type.
     * 
     * @return string The network type ID
     */
    public static function getNetworkTypeId() : string {
        return EntityIds::FALLING_BLOCK;
    }

    /**
     * @var string The text to display
     */
    private string $text;
    
    /**
     * @var bool Whether the entity is visible
     */
    private bool $isVisible = true;

    /**
     * Creates a new floating text entity.
     * 
     * @param Location $location The location to spawn this entity
     * @param string $text The text to display
     */
    public function __construct(Location $location, string $text) {
        $this->text = $text;
        $this->setCanSaveWithChunk(false);
        $this->keepMovement = true;
        $this->gravity = 0.0;
        $this->gravityEnabled = false;
        $this->drag = 0.0;
        $this->noClientPredictions = true;
        parent::__construct($location);
    }

    /**
     * Factory method to create and configure a new floating text entity.
     * 
     * @param Location $location The location to spawn this entity
     * @param string $text The text to display
     * @return self The configured entity instance
     */
    public static function create(Location $location, string $text): self {
        $entity = new self($location, $text);
        $entity->setNameTag($text);
        $entity->setNameTagAlwaysVisible(true);
        $entity->setScale(1.0);
        return $entity;
    }

    /**
     * Initializes the entity from NBT data.
     * 
     * @param CompoundTag $nbt The NBT data
     */
    protected function initEntity(CompoundTag $nbt) : void {
        parent::initEntity($nbt);
        $this->setNameTag($this->text);
        $this->setNameTagAlwaysVisible(true);
        $this->setScale(1.0);
    }

    /**
     * Returns the initial size information for this entity.
     * Makes the entity nearly invisible (0.01x0.01).
     * 
     * @return EntitySizeInfo The entity size info
     */
    protected function getInitialSizeInfo() : EntitySizeInfo {
        return new EntitySizeInfo(0.01, 0.01);
    }

    /**
     * Synchronizes entity data to the client.
     * Sets the block appearance to air and configures nametag visibility.
     * 
     * @param EntityMetadataCollection $properties The entity properties
     */
    protected function syncNetworkData(EntityMetadataCollection $properties) : void {
        parent::syncNetworkData($properties);
        $properties->setInt(EntityMetadataProperties::VARIANT, TypeConverter::getInstance()->getBlockTranslator()->internalIdToNetworkId(VanillaBlocks::AIR()->getStateId()));
        $properties->setGenericFlag(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, true);
    }

    /**
     * Makes the entity fireproof.
     * 
     * @return bool Always returns true
     */
    public function isFireProof() : bool {
        return true;
    }

    /**
     * Prevents the entity from being collided with.
     * 
     * @return bool Always returns false
     */
    public function canBeCollidedWith() : bool {
        return false;
    }

    /**
     * Disables block collision detection.
     */
    protected function checkBlockIntersections() : void {
        // No-op to prevent block collisions
    }

    /**
     * Prevents the entity from colliding with other entities.
     * 
     * @param Entity $entity The entity to check collision with
     * @return bool Always returns false
     */
    public function canCollideWith(Entity $entity) : bool {
        return false;
    }

    /**
     * Prevents the entity from being moved by water currents.
     * 
     * @return bool Always returns false
     */
    public function canBeMovedByCurrents() : bool {
        return false;
    }

    /**
     * Returns the initial drag multiplier.
     * 
     * @return float Always returns 0.0
     */
    protected function getInitialDragMultiplier() : float {
        return 0.0;
    }

    /**
     * Returns the initial gravity value.
     * 
     * @return float Always returns 0.0
     */
    protected function getInitialGravity() : float {
        return 0.0;
    }

    /**
     * Adjusts the position of the entity for rendering.
     * 
     * @param Vector3 $vector3 The base position
     * @return Vector3 The adjusted position (raised by 0.49 blocks)
     */
    public function getOffsetPosition(Vector3 $vector3) : Vector3 {
        return parent::getOffsetPosition($vector3)->add(0.0, 0.49, 0.0);
    }

    /**
     * Prevents the entity from taking damage.
     * 
     * @param EntityDamageEvent $source The damage event
     */
    public function attack(EntityDamageEvent $source) : void {
        $source->cancel();
    }

    /**
     * Disables the entity update cycle.
     * 
     * @param int $currentTick The current server tick
     * @return bool Always returns false
     */
    public function onUpdate(int $currentTick) : bool {
        return false;
    }

    /**
     * Disables the entity base tick processing.
     * 
     * @param int $tickDiff Number of ticks elapsed
     * @return bool Always returns false
     */
    protected function entityBaseTick(int $tickDiff = 1) : bool {
        return false;
    }

    /**
     * Sets the nametag text for this entity.
     * 
     * @param string $name The text to display
     */
    public function setNameTag(string $name) : void {
        parent::setNameTag($name);
        $this->text = $name;
        $this->sendData($this->hasSpawned, $this->getDirtyNetworkData());
        $this->getNetworkProperties()->clearDirtyProperties();
    }

    /**
     * Checks if the floating text is visible.
     * 
     * @return bool True if the entity is visible, false otherwise
     */
    public function isVisible() : bool {
        return $this->isVisible;
    }

    /**
     * Sets the visibility of the floating text.
     * 
     * @param bool $visible Whether the text should be visible
     */
    public function setVisible(bool $visible) : void {
        $this->isVisible = $visible;
        $this->setNameTagAlwaysVisible($visible);
    }
}